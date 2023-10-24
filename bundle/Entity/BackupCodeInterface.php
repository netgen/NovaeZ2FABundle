<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Entity;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface as SchebBackupCodeInterface;

interface BackupCodeInterface extends SchebBackupCodeInterface
{
    public function setBackupCodes(array $backupCodes): void;

    public function getBackupCodes(): array;
}
