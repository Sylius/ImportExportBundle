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

use Sylius\ImportExport\Serializer\DefaultSerializationGroups;
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
        $this->addImportConfiguration($rootNode);

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
                            ->defaultValue('sylius_import_export.provider.resource_data.orm')
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
                                    ->scalarNode('serialization_group')
                                        ->cannotBeEmpty()
                                        ->defaultValue(DefaultSerializationGroups::EXPORT_GROUP)
                                    ->end()
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

    private function addImportConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('import')
                    ->children()
                        ->scalarNode('files_directory')
                            ->defaultValue('%kernel.project_dir%/var/import')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('file_max_size')
                            ->defaultValue('50M')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('allowed_mime_types')
                            ->scalarPrototype()->end()
                            ->defaultValue(['application/json'])
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
                                    ->arrayNode('validation_groups')
                                        ->scalarPrototype()->end()
                                        ->defaultValue(['Default'])
                                    ->end()
                                ->end()
                            ->end()
                        ->end() // resources
                    ->end()
                ->end() // import
            ->end()
        ;
    }
}
