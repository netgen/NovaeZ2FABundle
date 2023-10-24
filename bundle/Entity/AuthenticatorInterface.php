<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Entity;

interface AuthenticatorInterface
{
    public function setAuthenticatorSecret(?string $googleAuthenticatorSecret): void;
}
