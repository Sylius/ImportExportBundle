<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\ImportExport\Grid\Checker\ExportableChecker;
use Sylius\ImportExport\Grid\Checker\ImportableChecker;
use Sylius\ImportExport\Grid\Checker\ImportableCheckerInterface;
use Sylius\ImportExport\Grid\Definition\CommonEventDispatchingArrayToDefinitionConverter;
use Sylius\ImportExport\Grid\Listener\ExportActionAdminGridListener;
use Sylius\ImportExport\Grid\Listener\ImportActionAdminGridListener;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('sylius_import_export.grid.checker.exportable', ExportableChecker::class)
        ->args([
            service('sylius.resource_registry'),
            '%sylius_import_export.export.resources%',
        ]);

    $services->set('sylius_import_export.grid.checker.importable', ImportableChecker::class)
        ->args([
            service('sylius.resource_registry'),
            '%sylius_import_export.import.resources%',
        ]);

    $services->alias(ImportableCheckerInterface::class, 'sylius_import_export.grid.checker.importable');

    $services->set('sylius_import_export.grid.array_to_definition_converter.common_event_dispatching', CommonEventDispatchingArrayToDefinitionConverter::class)
        ->decorate('sylius.grid.array_to_definition_converter')
        ->args([
            service('event_dispatcher'),
            service('.inner'),
        ]);

    $services->set('sylius_import_export.grid.listener.admin.export_action', ExportActionAdminGridListener::class)
        ->args([
            service('request_stack'),
            service('sylius.section_resolver.uri_based')->nullOnInvalid(),
            service('sylius_import_export.grid.checker.exportable'),
        ])
        ->tag('kernel.event_listener', ['event' => 'sylius.grid.common', 'method' => 'addExportActions']);

    $services->set('sylius_import_export.grid.listener.admin.import_action', ImportActionAdminGridListener::class)
        ->args([
            service('request_stack'),
            service('sylius.section_resolver.uri_based')->nullOnInvalid(),
            service('sylius_import_export.grid.checker.importable'),
        ])
        ->tag('kernel.event_listener', ['event' => 'sylius.grid.common', 'method' => 'addImportActions']);
};
