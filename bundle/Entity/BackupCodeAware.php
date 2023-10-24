<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\Entity;

trait BackupCodeAware
{
    private array $backupCodes = [];

    public function isBackupCode(string $code): bool
    {
        return in_array((int) $code, $this->backupCodes, true);
    }

    public function invalidateBackupCode(string $code): void
    {
        $key = array_search((int) $code, $this->backupCodes, true);
        if (false !== $key) {
            unset($this->backupCodes[$key]);
        }
    }

    public function setBackupCodes(array $backupCodes): void
    {
        $this->backupCodes = $backupCodes;
    }

    public function getBackupCodes(): array
    {
        return $this->backupCodes;
    }
}
