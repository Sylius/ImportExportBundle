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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SyliusImportExportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->processConfiguration(new Configuration(), $configs);

        $this->processExportConfig($container, $configuration);
        $this->processImportConfig($container, $configuration);

        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/config/'));
        $loader->load('services.xml');
    }

    private function processExportConfig(ContainerBuilder $container, array &$config): void
    {
        $defaultProvider = $config['export']['default_provider'];
        $defaultSection = $config['export']['default_section'];

        foreach ($config['export']['resources'] as $name => &$resource) {
            if (null === $resource['provider']) {
                $resource['provider'] = $defaultProvider;
            }
            if ([] === $resource['sections']) {
                $resource['sections'][] = $defaultSection;
            }
        }

        $container->setParameter('sylius_import_export.export.default_provider', $defaultProvider);
        $container->setParameter('sylius_import_export.export.resources', $config['export']['resources']);
        $container->setParameter('sylius_import_export.export_files_directory', '%kernel.project_dir%/var/export');
    }

    private function processImportConfig(ContainerBuilder $container, array &$config): void
    {
        if (!isset($config['import'])) {
            $config['import'] = [];
        }

        $filesDirectory = $config['import']['files_directory'] ?? '%kernel.project_dir%/var/import';
        $fileMaxSize = $config['import']['file_max_size'] ?? '50M';
        $allowedMimeTypes = $config['import']['allowed_mime_types'] ?? ['application/json'];
        $resources = $config['import']['resources'] ?? [];

        foreach ($resources as &$resource) {
            if (!isset($resource['validation_groups'])) {
                $resource['validation_groups'] = ['Default'];
            }
        }

        $container->setParameter('sylius_import_export.import_files_directory', $filesDirectory);
        $container->setParameter('sylius_import_export.import.file_max_size', $fileMaxSize);
        $container->setParameter('sylius_import_export.import.allowed_mime_types', $allowedMimeTypes);
        $container->setParameter('sylius_import_export.import.resources', $resources);
    }
}
