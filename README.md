<p align="center">
    <a href="https://sylius.com" target="_blank">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://media.sylius.com/sylius-logo-800-dark.png">
          <source media="(prefers-color-scheme: light)" srcset="https://media.sylius.com/sylius-logo-800.png">
          <img alt="Sylius Logo." src="https://media.sylius.com/sylius-logo-800.png">
        </picture>
    </a>
</p>

<h1 align="center">Grid Import/Export Bundle</h1>

The ImportExportBundle allows for easy and decoupled data migration to and from various mediums.<br>
It works by relying on the Sylius [Resource](https://github.com/sylius/syliusresourcebundle) and [Grid](https://github.com/Sylius/syliusgridbundle) systems for resolving and providing data.

## Export

### Functionality

The main and bulk grid actions get automatically injected into configured resources' grids.

Supported formats:
- json
- csv

#### Main export action

It respects currently applied filters when exporting resources.

![Screenshot showing order grid with main action focused](docs/images/screenshot_order_main_action.png)

#### Bulk export action

In cases when specifying filters is not enough, or you want just a subset of the resource, in comes the bulk action. 

![Screenshot showing order grid with bulk action focused](docs/images/screenshot_order_bulk_action.png)

#### Processes

Here you can manage currently running and already processed exports as well as download the exported data.

![Screenshot showing processes index](docs/images/screenshot_process_index.png)

## Installation

#### Beware!

This installation instruction assumes that you're using Symfony Flex. If you don't, take a look at the
[legacy installation instruction](docs/legacy_installation.md). However, we strongly encourage you to use
Symfony Flex, it's much quicker!

1. Require plugin with composer:

    ```bash
    composer require sylius/grid-import-export-bundle
    ```

   > Remember to allow community recipes with `composer config extra.symfony.allow-contrib true` or during plugin installation process

2. Apply migrations to your database:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

3. Configure export for resources:

    ```yaml
    # config/packages/sylius_import_export.yaml
    sylius_import_export:
        export:
            resources:
                sylius.order: ~
                app.brand:
                    sections:
                        - 'Sylius\Bundle\AdminBundle\SectionResolver\AdminSection'
                    provider: 'sylius_import_export.provider.resource_data.dbal'
    ```
   For a more detailed overview check the [configuration reference](docs/configuration_reference.md).

## Exported files

By default, when a resource gets exported, a file is saved on the server. The save directory is specified
with the `%sylius_import_export.export_files_directory%` parameter, that can be overridden if needed.

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly.
Instead, all security issues must be sent to `security@sylius.com`.
