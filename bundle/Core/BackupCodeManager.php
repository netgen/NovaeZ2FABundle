<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Core;

use Ibexa\Core\MVC\Symfony\Security\User;
use Netgen\Bundle\Ibexa2FABundle\Entity\BackupCodeInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManagerInterface;

use function array_values;
use function json_encode;

final class BackupCodeManager implements BackupCodeManagerInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function isBackupCode($user, string $code): bool
    {
        if ($user instanceof BackupCodeInterface) {
            return $user->isBackupCode($code);
        }

        return false;
    }

    public function invalidateBackupCode($user, string $code): void
    {
        if ($user instanceof BackupCodeInterface) {
            /* @var User|BackupCodeInterface $user */
            $user->invalidateBackupCode($code);
            $this->userRepository->updateBackupCodes(
                $user->getAPIUser()->getUserId(),
                json_encode(array_values($user->getBackupCodes())),
            );
        }
    }
}
