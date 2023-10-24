<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\DependencyInjection\Security;

use Ibexa\Bundle\Core\DependencyInjection\Security\PolicyProvider\YamlPolicyProvider;

class PolicyProvider extends YamlPolicyProvider
{
    protected function getFiles(): array
    {
        return [
            __DIR__ . '/../../Resources/config/policies.yaml',
        ];
    }
}
