<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\ImportExport\Provider\Parameters\GridExportParametersProvider;
use Sylius\ImportExport\Provider\Registry\ResourceDataProviderRegistry;
use Sylius\ImportExport\Provider\ResourceData\DbalResourceDataProvider;
use Sylius\ImportExport\Provider\ResourceData\ORMResourceDataProvider;
use Sylius\ImportExport\Provider\ResourceIdentifierProvider;
use Sylius\ImportExport\Provider\ResourceIds\CompositeResourceIdsProvider;
use Sylius\ImportExport\Provider\ResourceIds\RequestBasedResourcesIdsProvider;
use Sylius\ImportExport\Provider\ResourceIds\StaticResourceIdsProvider;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_import_export.provider.resource_identifier', ResourceIdentifierProvider::class)
        ->args([service('doctrine.orm.entity_manager')]);

    $services->set('sylius_import_export.registry.resource_data_provider', ResourceDataProviderRegistry::class)
        ->args([
            '%sylius_import_export.export.resources%',
            tagged_iterator('sylius_import_export.resource_data_provider', 'service'),
        ]);

    $services->set('sylius_import_export.provider.resource_data.dbal', DbalResourceDataProvider::class)
        ->args([
            service('sylius_import_export.provider.resource_identifier'),
            service('doctrine.orm.entity_manager'),
        ])
        ->tag('sylius_import_export.resource_data_provider');

    $services->set('sylius_import_export.provider.resource_data.orm', ORMResourceDataProvider::class)
        ->args([
            service('sylius_import_export.provider.resource_identifier'),
            service('doctrine.orm.entity_manager'),
            service('serializer'),
        ])
        ->tag('sylius_import_export.resource_data_provider');

    $services->set('sylius_import_export.provider.resources_ids', CompositeResourceIdsProvider::class)
        ->args([tagged_iterator('sylius_import_export.resource_ids_provider')]);

    $services->set('sylius_import_export.provider.resources_ids.request_based', RequestBasedResourcesIdsProvider::class)
        ->args([
            service('sylius.resource_controller.request_configuration_factory'),
            service('sylius.resource_controller.resources_collection_provider'),
            service('sylius.grid.data_source_provider'),
            service('sylius.resource_controller.parameters_parser'),
            service('sylius_import_export.provider.resource_identifier'),
            service('doctrine.orm.entity_manager'),
        ])
        ->tag('sylius_import_export.resource_ids_provider');

    $services->set('sylius_import_export.provider.resources_ids.static', StaticResourceIdsProvider::class)
        ->tag('sylius_import_export.resource_ids_provider', ['priority' => 512]);

    $services->set('sylius_import_export.provider.grid_export_parameters', GridExportParametersProvider::class)
        ->args([
            service('sylius.grid.chain_provider'),
            service('sylius.resource_controller.parameters_parser'),
            '%sylius_import_export.export.resources%',
        ]);
};
