<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\DependencyInjection;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class NovaeZ2FAExtension extends Extension
{
    public function getAlias(): string
    {
        return Configuration::NAMESPACE;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('default_settings.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $processor = new ConfigurationProcessor($container, Configuration::NAMESPACE);
        $processor->mapSetting('2fa_mobile_method', $config);
        $processor->mapSetting('2fa_email_method_enabled', $config);
        $processor->mapSetting('2fa_force_setup', $config);
        $processor->mapConfigArray('config', $config);
    }
}
