<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\ImportExport\Importer\JsonImporter;
use Sylius\ImportExport\Resolver\ImporterResolver;
use Sylius\ImportExport\Resolver\ImporterResolverInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_import_export.importer.json', JsonImporter::class)
        ->args([service('serializer.encoder.json')])
        ->tag('sylius.import_export.importer', ['format' => 'json']);

    $services->set('sylius_import_export.importer_resolver', ImporterResolver::class)
        ->args([tagged_locator('sylius.import_export.importer', 'format')]);

    $services->alias(ImporterResolverInterface::class, 'sylius_import_export.importer_resolver');
};
