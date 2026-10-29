<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\ImportExport\Controller\DownloadExportAction;
use Sylius\ImportExport\Controller\ExportAction;
use Sylius\ImportExport\Controller\ImportAction;
use Sylius\ImportExport\Factory\ProcessFactory;
use Sylius\ImportExport\Manager\BatchedExportDataManager;
use Sylius\ImportExport\Menu\AdminImportExportMenuListener;
use Sylius\ImportExport\Menu\MenuReorder;
use Sylius\ImportExport\Messenger\Handler\CreateExportProcessHandler;
use Sylius\ImportExport\Messenger\Handler\CreateImportProcessHandler;
use Sylius\ImportExport\Messenger\Handler\ExportCommandHandler;
use Sylius\ImportExport\Messenger\Handler\ExportCompletedHandler;
use Sylius\ImportExport\Messenger\Handler\ImportCommandHandler;
use Sylius\ImportExport\Messenger\Middleware\ExportBatchTrackingMiddleware;
use Sylius\ImportExport\Processor\BatchProcessor;
use Sylius\ImportExport\Serializer\ExportAwareItemNormalizer;
use Sylius\ImportExport\Twig\Component\ExportResourceFormComponent;
use Sylius\ImportExport\Twig\Component\ImportResourceFormComponent;
use Sylius\ImportExport\Uploader\ImportFileUploader;
use Sylius\ImportExport\Validator\ImportValidator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $container->import('services/**/*.php');

    $services->set('sylius_import_export.messenger.command_handler.create_export_process', CreateExportProcessHandler::class)
        ->args([
            service('sylius_import_export.factory.process'),
            service('sylius_import_export.repository.process_export'),
            service('sylius_import_export.export.command_bus'),
            service('sylius_import_export.export.batched_data_manager'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius.command_bus']);

    $services->set('sylius_import_export.messenger.command_handler.export', ExportCommandHandler::class)
        ->args([
            service('sylius.resource_registry'),
            service('sylius_import_export.repository.process_export'),
            service('sylius_import_export.registry.resource_data_provider'),
            service('sylius_import_export.exporter_resolver'),
            service('sylius_import_export.export.batched_data_manager'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius_import_export.export.command_bus']);

    $services->set('sylius_import_export.messenger.event_handler.export_completed', ExportCompletedHandler::class)
        ->args([
            service('sylius_import_export.repository.process_export'),
            service('sylius_import_export.exporter_resolver'),
            service('sylius_import_export.export.batched_data_manager'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius_import_export.export.event_bus']);

    $services->set('sylius_import_export.messenger.middleware.export_batch_tracking', ExportBatchTrackingMiddleware::class)
        ->args([
            service('sylius_import_export.repository.process_export'),
            service('sylius_import_export.export.event_bus'),
        ])
        ->tag('messenger.middleware');

    $services->set('sylius_import_export.messenger.command_handler.create_import_process', CreateImportProcessHandler::class)
        ->args([
            service('sylius_import_export.factory.process'),
            service('sylius_import_export.repository.process_import'),
            service('sylius_import_export.import.command_bus'),
            service('sylius_import_export.importer_resolver'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius_import_export.import.command_bus']);

    $services->set('sylius_import_export.validator.import', ImportValidator::class)
        ->args([service('validator')]);

    $services->set('sylius_import_export.processor.batch', BatchProcessor::class)
        ->args([
            service('sylius_import_export.denormalizer.registry'),
            service('doctrine.orm.entity_manager'),
            service('sylius.resource_registry'),
            service('sylius_import_export.validator.import'),
        ]);

    $services->set('sylius_import_export.messenger.command_handler.import', ImportCommandHandler::class)
        ->args([
            service('sylius_import_export.repository.process_import'),
            service('doctrine.orm.entity_manager'),
            service('sylius_import_export.processor.batch'),
        ])
        ->tag('messenger.message_handler', ['bus' => 'sylius_import_export.import.command_bus']);

    $services->set('sylius_import_export.twig.component.export_resource', ExportResourceFormComponent::class)
        ->args([
            service('form.factory'),
            '%sylius_import_export.export.form_class%',
        ])
        ->tag('sylius.twig_component', [
            'key' => 'sylius_import_export:admin:export',
            'template' => '@SyliusImportExport\admin\import_export\component\main_export_form.html.twig',
        ]);

    $services->set('sylius_import_export.admin.twig.component.bulk_export_resource', ExportResourceFormComponent::class)
        ->args([
            service('form.factory'),
            '%sylius_import_export.export.form_class%',
        ])
        ->tag('sylius.twig_component', [
            'key' => 'sylius_import_export:admin:bulk_export',
            'template' => '@SyliusImportExport\admin\import_export\component\bulk_export_form.html.twig',
        ]);

    $services->set('sylius_import_export.controller.export_action', ExportAction::class)
        ->public()
        ->args([
            service('sylius.resource_registry'),
            service('sylius_import_export.provider.resources_ids'),
            service('sylius_import_export.provider.grid_export_parameters'),
            service('form.factory'),
            service('sylius.command_bus'),
            '%sylius_import_export.export.form_class%',
        ])
        ->tag('controller.service_arguments');

    $services->set('sylius_import_export.controller.download_export_action', DownloadExportAction::class)
        ->public()
        ->args([service('sylius_import_export.repository.process_export')])
        ->tag('controller.service_arguments');

    $services->set('sylius_import_export.uploader.import_file', ImportFileUploader::class)
        ->args(['%sylius_import_export.import_files_directory%']);

    $services->set('sylius_import_export.controller.import_action', ImportAction::class)
        ->public()
        ->args([
            service('form.factory'),
            service('sylius_import_export.import.command_bus'),
            '%sylius_import_export.import.form_class%',
            service('sylius_import_export.uploader.import_file'),
            service('sylius.resource_registry'),
            '%sylius_import_export.import.resources%',
        ])
        ->tag('controller.service_arguments');

    $services->set('sylius_import_export.factory.process', ProcessFactory::class)
        ->args([
            service('sylius_import_export.factory.process_export'),
            service('sylius_import_export.factory.process_import'),
        ]);

    $services->set('sylius_import_export.export.batched_data_manager', BatchedExportDataManager::class)
        ->args(['%sylius_import_export.export_files_directory%']);

    $services->set('sylius_import_export.menu.admin_listener', AdminImportExportMenuListener::class)
        ->args([service('sylius_import_export.menu.reorder')])
        ->tag('kernel.event_listener', ['method' => 'buildMenu', 'event' => 'sylius.menu.admin.main']);

    $services->set('sylius_import_export.menu.reorder', MenuReorder::class);

    $services->set('sylius_import_export.normalizer.item', ExportAwareItemNormalizer::class)
        ->parent('api_platform.serializer.normalizer.item')
        ->decorate('api_platform.serializer.normalizer.item')
        ->arg('$decorated', service('.inner'));

    $services->set('sylius_import_export.twig.component.import_resource', ImportResourceFormComponent::class)
        ->args([
            service('form.factory'),
            '%sylius_import_export.import.form_class%',
        ])
        ->tag('twig.component', [
            'key' => 'sylius_import_export:admin:import',
            'template' => '@SyliusImportExport\admin\import_export\component\main_import_form.html.twig',
        ]);
};
