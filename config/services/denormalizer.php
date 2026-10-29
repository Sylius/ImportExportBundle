<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\ImportExport\Denormalizer\DefaultResourceDenormalizer;
use Sylius\ImportExport\Denormalizer\DenormalizerRegistry;
use Sylius\ImportExport\Denormalizer\DenormalizerRegistryInterface;
use Sylius\ImportExport\Denormalizer\EntityRelationDenormalizer;
use Sylius\ImportExport\Denormalizer\ResourceDenormalizerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_import_export.denormalizer.entity_relation', EntityRelationDenormalizer::class)
        ->args([service('doctrine.orm.entity_manager')])
        ->tag('serializer.normalizer', ['priority' => 64]);

    $services->set('sylius_import_export.denormalizer.default_resource', DefaultResourceDenormalizer::class)
        ->args([service('serializer')]);

    $services->alias(ResourceDenormalizerInterface::class, 'sylius_import_export.denormalizer.default_resource');

    $services->set('sylius_import_export.denormalizer.registry', DenormalizerRegistry::class)
        ->args([
            service('sylius_import_export.denormalizer.default_resource'),
            tagged_iterator('sylius_import_export.resource_denormalizer'),
        ]);

    $services->alias(DenormalizerRegistryInterface::class, 'sylius_import_export.denormalizer.registry');
};
