<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\ImportExport\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_import_export');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $this->addExportConfiguration($rootNode);

        return $treeBuilder;
    }

    private function addExportConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('export')
                    ->isRequired()
                    ->children()
                        ->scalarNode('default_provider')
                            ->defaultValue('sylius_import_export.provider.resource_data.grid')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('default_section')
                            ->defaultValue('admin')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('resources')
                            ->useAttributeAsKey('name')
                            ->normalizeKeys(false)
                            ->arrayPrototype()
                                ->beforeNormalization()
                                    ->ifNull()
                                    ->then(function () {
                                        return [];
                                    })
                                ->end()
                                ->children()
                                    ->scalarNode('provider')
                                        ->defaultNull()
                                    ->end()
                                    ->arrayNode('sections')
                                        ->scalarPrototype()->end()
                                        ->defaultValue([])
                                    ->end()
                                ->end()
                            ->end()
                        ->end() // resources
                    ->end()
                ->end() // export
            ->end()
        ;
    }
}
