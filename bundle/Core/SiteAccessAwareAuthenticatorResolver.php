<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Core;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Security\User;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Ibexa\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use Netgen\Bundle\Ibexa2FABundle\DependencyInjection\Configuration;
use Netgen\Bundle\Ibexa2FABundle\Entity\AuthenticatorInterface;
use Netgen\Bundle\Ibexa2FABundle\Entity\BackupCodeInterface;
use Netgen\Bundle\Ibexa2FABundle\Entity\UserEmailAuth;
use Netgen\Bundle\Ibexa2FABundle\Entity\UserGoogleAuthSecret;
use Netgen\Bundle\Ibexa2FABundle\Entity\UserTotpAuthSecret;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticator;

use function is_array;
use function json_decode;
use function json_encode;
use function random_int;

final class SiteAccessAwareAuthenticatorResolver implements SiteAccessAware
{
    private ?SiteAccess $siteAccess;

    private string $method;

    private array $config;

    private ConfigResolverInterface $configResolver;

    private GoogleAuthenticator $googleAuthenticator;

    private TotpAuthenticator $totpAuthenticator;

    private UserRepository $userRepository;

    private bool $backupCodesEnabled;

    private bool $emailMethodEnabled;

    private bool $forceSetup;

    public function __construct(
        ConfigResolverInterface $configResolver,
        GoogleAuthenticator $googleAuthenticator,
        TotpAuthenticator $totpAuthenticator,
        UserRepository $userRepository,
        bool $backupCodesEnabled,
    ) {
        $this->configResolver = $configResolver;
        $this->googleAuthenticator = $googleAuthenticator;
        $this->totpAuthenticator = $totpAuthenticator;
        $this->userRepository = $userRepository;
        $this->backupCodesEnabled = $backupCodesEnabled;
    }

    /**
     * @required
     */
    public function setSiteAccess(?SiteAccess $siteAccess = null): void
    {
        $this->siteAccess = $siteAccess;
        $this->setConfig();
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function isEmailMethodEnabled(): bool
    {
        return $this->emailMethodEnabled;
    }

    public function isForceSetup(): bool
    {
        return $this->forceSetup;
    }

    public function getUserAuthenticatorEntity(User $user)
    {
        if ('email' === $this->method) {
            return new UserEmailAuth($user->getAPIUser(), $user->getRoles());
        }
        if ('google' === $this->method) {
            return new UserGoogleAuthSecret($user->getAPIUser(), $user->getRoles());
        }
        if ('microsoft' === $this->method) {
            return new UserTotpAuthSecret($user->getAPIUser(), $user->getRoles());
        }

        return new UserTotpAuthSecret($user->getAPIUser(), $user->getRoles(), $this->config);
    }

    public function getUserForDecorator(User $user): User
    {
        $userAuthData = $this->getUserAuthData($user);

        if (false === $userAuthData) {
            return $user;
        }

        if ($userAuthData['email_authentication']) {
            $this->method = 'email';
        }

        if ('email' !== $this->method && empty($userAuthData["{$this->method}_authentication_secret"])) {
            return $user;
        }

        $authenticatorEntity = $this->getUserAuthenticatorEntity($user);

        if ('email' === $this->method) {
            $authenticatorEntity->setEmailAuthCode($userAuthData['email_authentication_code']);
        } else {
            $authenticatorEntity->setAuthenticatorSecret($userAuthData["{$this->method}_authentication_secret"]);
            $authenticatorEntity->setBackupCodes(json_decode($userAuthData['backup_codes']) ?? []);
        }

        return $authenticatorEntity;
    }

    public function getAuthenticator()
    {
        if ('google' === $this->method) {
            return $this->googleAuthenticator;
        }

        return $this->totpAuthenticator;
    }

    public function validateCodeAndUpdateUser(User $user, array $formData): array
    {
        /* @var User|TwoFactorInterface|AuthenticatorInterface|BackupCodeInterface $user */
        $user->setAuthenticatorSecret($formData['secretKey']);
        if ($this->getAuthenticator()->checkCode($user, $formData['code'])) {
            if ($this->backupCodesEnabled) {
                // Generating backup codes
                $backupCodes = [
                    random_int(100000, 999999),
                    random_int(100000, 999999),
                    random_int(100000, 999999),
                    random_int(100000, 999999),
                    random_int(100000, 999999),
                    random_int(100000, 999999),
                ];

                $user->setBackupCodes($backupCodes);
            }

            $this->userRepository->insertUpdateUserAuthSecret(
                $user->getAPIUser()->getUserId(),
                $formData['secretKey'],
                $this->method,
                isset($backupCodes) ? json_encode($backupCodes) : '',
            );

            return [
                'valid' => true,
                'backupCodes' => $backupCodes ?? [],
            ];
        }

        return [
            'valid' => false,
        ];
    }

    public function setEmailAuthentication(User $user): void
    {
        $this->method = 'email';
        $this->userRepository->insertUpdateEmailAuthentication($user->getAPIUser()->getUserId());
    }

    public function checkIfUserSecretOrEmailExists(User $user): bool
    {
        $userAuthData = $this->getUserAuthData($user);

        if (false === $userAuthData) {
            return false;
        }

        if ($userAuthData['email_authentication']) {
            $this->method = 'email';

            return true;
        }

        return is_array($userAuthData)
               && (
                   !empty($userAuthData['google_authentication_secret'])
                   || !empty($userAuthData['totp_authentication_secret'])
                   || !empty($userAuthData['microsoft_authentication_secret'])
               );
    }

    public function getUserAuthData(User $user)
    {
        return $this->userRepository->getUserAuthData($user->getAPIUser()->getUserId());
    }

    public function deleteUserAuthSecretAndEmail(User $user): void
    {
        $this->userRepository->deleteUserAuthSecretAndEmail($user->getAPIUser()->getUserId(), $this->method);
    }

    private function setConfig(): void
    {
        $this->method = $this->configResolver->getParameter(
            '2fa_mobile_method',
            Configuration::NAMESPACE,
            $this->siteAccess->name,
        );
        $this->config = $this->configResolver->getParameter(
            'config',
            Configuration::NAMESPACE,
            $this->siteAccess->name,
        );
        $this->emailMethodEnabled = $this->configResolver->getParameter(
            '2fa_email_method_enabled',
            Configuration::NAMESPACE,
            $this->siteAccess->name,
        );
        $this->forceSetup = $this->configResolver->getParameter(
            '2fa_force_setup',
            Configuration::NAMESPACE,
            $this->siteAccess->name,
        );
    }
}
