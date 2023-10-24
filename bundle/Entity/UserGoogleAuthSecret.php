<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Entity;

use Ibexa\Contracts\Core\Repository\Values\User\User as APIUser;
use Ibexa\Core\MVC\Symfony\Security\User;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

final class UserGoogleAuthSecret extends User implements TwoFactorInterface, BackupCodeInterface, AuthenticatorInterface
{
    use BackupCodeAware;

    private ?string $secret;

    public function __construct(APIUser $user, array $roles = [])
    {
        parent::__construct($user, $roles);
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return null !== $this->secret;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getUsername();
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->secret;
    }

    public function setAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->secret = $googleAuthenticatorSecret;
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
}
