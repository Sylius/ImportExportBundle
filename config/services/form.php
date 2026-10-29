<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\ImportExport\Form\ChoiceLoader\ExportFormatsChoiceLoader;
use Sylius\ImportExport\Form\Type\ExportResourceType;
use Sylius\ImportExport\Form\Type\ImportResourceType;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $parameters->set('sylius_import_export.export.form_class', ExportResourceType::class);
    $parameters->set('sylius_import_export.import.form_class', ImportResourceType::class);

    $services->set('sylius_import_export.form.choice_loader.export_formats', ExportFormatsChoiceLoader::class)
        ->args([tagged_iterator('sylius.grid_import_export.exporter', 'format')]);

    $services->set('sylius_import_export.form.type.export', ExportResourceType::class)
        ->args([service('sylius_import_export.form.choice_loader.export_formats')])
        ->tag('form.type');

    $services->set('sylius_import_export.form.type.import', ImportResourceType::class)
        ->args([
            '%sylius_import_export.import.file_max_size%',
            '%sylius_import_export.import.allowed_mime_types%',
        ])
        ->tag('form.type');
};
