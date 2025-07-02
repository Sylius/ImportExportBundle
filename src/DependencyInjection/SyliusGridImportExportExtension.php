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

namespace Sylius\GridImportExport\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SyliusGridImportExportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->processConfiguration(new Configuration(), $configs);

        $this->processExportConfig($container, $configuration);

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
    }
}
