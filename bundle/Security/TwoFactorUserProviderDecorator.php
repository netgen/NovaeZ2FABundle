<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Security;

use Ibexa\Contracts\Core\Repository\Values\User\User as APIUser;
use Ibexa\Core\MVC\Symfony\Security\User;
use Ibexa\Core\MVC\Symfony\Security\User\APIUserProviderInterface;
use Netgen\Bundle\Ibexa2FABundle\Core\SiteAccessAwareAuthenticatorResolver;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class TwoFactorUserProviderDecorator implements UserProviderInterface, APIUserProviderInterface
{
    private UserProviderInterface|APIUserProviderInterface $provider;

    private SiteAccessAwareAuthenticatorResolver $saAuthenticatorResolver;

    public function __construct(
        UserProviderInterface $provider,
        SiteAccessAwareAuthenticatorResolver $saAuthenticatorResolver,
    ) {
        $this->provider = $provider;
        $this->saAuthenticatorResolver = $saAuthenticatorResolver;
    }

    public function loadUserByAPIUser(APIUser $apiUser): User
    {
        return $this->provider->loadUserByAPIUser($apiUser);
    }

    public function loadUserByUsername(string $username)
    {
        $user = $this->provider->loadUserByUsername($username);

        if ($user instanceof User) {
            return $this->saAuthenticatorResolver->getUserForDecorator($user);
        }

        return $user;
    }

    public function loadUserByIdentifier(string $identifier)
    {
        return $this->loadUserByUsername($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->provider->refreshUser($user);
    }

    public function supportsClass(string $class): bool
    {
        return $this->provider->supportsClass($class);
    }
}
