<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Entity;

use Ibexa\Contracts\Core\Repository\Values\User\User as APIUser;
use Ibexa\Core\MVC\Symfony\Security\User;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;

final class UserTotpAuthSecret extends User implements TwoFactorInterface, BackupCodeInterface, AuthenticatorInterface
{
    use BackupCodeAware;

    private const DEFAULT_ALGORITHM = TotpConfiguration::ALGORITHM_SHA1;
    private const DEFAULT_PERIOD = 30;
    private const DEFAULT_DIGITS = 6;

    private ?string $secret;

    private array $config;

    public function __construct(APIUser $user, array $roles = [], array $config = [])
    {
        parent::__construct($user, $roles);
        $this->config = $config;
    }

    public function __serialize(): array
    {
        return [
            'reference' => $this->getAPIUserReference(),
            'roles' => $this->getRoles(),
            'secret' => $this->secret,
            'backupCodes' => $this->backupCodes,
        ];
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return (bool) $this->secret;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->getUsername();
    }

    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface
    {
        // You could persist the other configuration options in the user entity to make it individual per user.
        return new TotpConfiguration(
            $this->secret,
            $this->config['algorithm'] ?? self::DEFAULT_ALGORITHM,
            $this->config['period'] ?? self::DEFAULT_PERIOD,
            $this->config['digits'] ?? self::DEFAULT_DIGITS,
        );
    }

    public function setAuthenticatorSecret(?string $totpAuthenticatorSecret): void
    {
        $this->secret = $totpAuthenticatorSecret;
    }
}
