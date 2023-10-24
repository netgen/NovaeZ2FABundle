<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle;

use LogicException;
use Netgen\Bundle\Ibexa2FABundle\DependencyInjection\NovaeZ2FAExtension;
use Netgen\Bundle\Ibexa2FABundle\DependencyInjection\Security\PolicyProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenIbexa2FABundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $ibexaExtension = $container->getExtension('ibexa');
        $ibexaExtension->addPolicyProvider(new PolicyProvider());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $extension = new NovaeZ2FAExtension();

            $this->extension = $extension;
        }

        return $this->extension;
    }
}
