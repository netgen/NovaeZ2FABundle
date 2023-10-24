<?php

declare(strict_types=1);

namespace Netgen\Bundle\Ibexa2FABundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SAConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SAConfiguration
{
    public const NAMESPACE = 'netgen_ibexa2fa';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(self::NAMESPACE);
        $rootNode = $treeBuilder->getRootNode();
        $systemNode = $this->generateScopeBaseNode($rootNode);
        $systemNode
            ->enumNode('2fa_mobile_method')->values(['google', 'totp', 'microsoft', null])->defaultNull()->end()
            ->arrayNode('config')
                ->children()
                    ->enumNode('algorithm')->values(
                        [
                            TotpConfiguration::ALGORITHM_MD5,
                            TotpConfiguration::ALGORITHM_SHA1,
                            TotpConfiguration::ALGORITHM_SHA256,
                            TotpConfiguration::ALGORITHM_SHA512,
                        ],
                    )->end()
                    ->integerNode('period')->end()
                    ->integerNode('digits')->end()
                ->end()
            ->end()
            ->booleanNode('2fa_email_method_enabled')->defaultTrue()->end()
            ->booleanNode('2fa_force_setup')->defaultTrue()->end()
        ->end();

        return $treeBuilder;
    }
}
