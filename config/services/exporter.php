<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\ImportExport\Exporter\CsvExporter;
use Sylius\ImportExport\Exporter\JsonExporter;
use Sylius\ImportExport\Resolver\ExporterResolver;
use Sylius\ImportExport\Resolver\ExporterResolverInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $parameters->set('sylius_import_export.export_files_directory', '%kernel.project_dir%/etc/export');

    $services->set('sylius_import_export.exporter.csv', CsvExporter::class)
        ->args(['%sylius_import_export.export_files_directory%'])
        ->tag('sylius.grid_import_export.exporter', ['format' => 'csv']);

    $services->set('sylius_import_export.exporter.json', JsonExporter::class)
        ->args(['%sylius_import_export.export_files_directory%'])
        ->tag('sylius.grid_import_export.exporter', ['format' => 'json']);

    $services->set('sylius_import_export.exporter_resolver', ExporterResolver::class)
        ->args([tagged_locator('sylius.grid_import_export.exporter', 'format')]);

    $services->alias(ExporterResolverInterface::class, 'sylius_import_export.exporter_resolver');
};
