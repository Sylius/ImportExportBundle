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

## Features

- **Export**: Generate data exports in JSON/CSV formats with grid actions
- **Import**: Import data from JSON files with validation and error handling
- **Process Management**: Track import/export processes with status monitoring
- **Validation**: Configurable validation groups for import data
- **Async Processing**: Background processing via Symfony Messenger (configurable)

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

## Import

### Functionality

The import feature provides a user-friendly way to import data from JSON files directly through the admin interface.

Supported formats:
- json (with plans for additional formats)

#### Main import action

Import actions are automatically injected into configured resources' grids, allowing administrators to upload and import data files.

#### Import Process

1. **File Upload**: Upload JSON files through the admin interface
2. **Validation**: Data is validated using configurable validation groups
3. **Processing**: Import runs via Symfony Messenger (synchronously by default, can be configured for async)
4. **Status Tracking**: Monitor import progress and view results
5. **Error Handling**: Failed imports are tracked with detailed error messages

#### Validation

The import system supports configurable validation groups to ensure data integrity:

- **Default validation**: Uses entity validation constraints
- **Custom validation groups**: Configure specific validation rules per resource
- **Error reporting**: Detailed validation error messages for troubleshooting

#### Process Management

Import processes are tracked with the following statuses:
- `processing`: Import is currently running
- `success`: Import completed successfully
- `failed`: Import failed with error details

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

3. Configure export and import for resources:

    ```yaml
    # config/packages/sylius_import_export.yaml
    sylius_import_export:
        export:
            resources:
                sylius.order: ~
                app.brand:
                    serialization_group: 'app:brand:export'
                    sections:
                        - 'Sylius\Bundle\AdminBundle\SectionResolver\AdminSection'
                    provider: 'sylius_import_export.provider.resource_data.orm'
        import:
            resources:
                sylius.order: ~
                app.brand:
                    validation_groups: ['Default', 'import']
    ```
   For a more detailed overview check the [configuration reference](docs/configuration_reference.md).

## File Storage

### Export Files

By default, when a resource gets exported, a file is saved on the server. The save directory is specified
with the `%sylius_import_export.export_files_directory%` parameter, that can be overridden if needed.

### Import Files

Import files are temporarily stored on the server during processing. The save directory is specified
with the `%sylius_import_export.import_files_directory%` parameter, that can be overridden if needed.

Both file directories can be configured:

```yaml
# config/packages/sylius_import_export.yaml
sylius_import_export:
    export:
        files_directory: '%kernel.project_dir%/var/export'
    import:
        files_directory: '%kernel.project_dir%/var/import'
        file_max_size: '50M'
        allowed_mime_types: ['application/json']
```

**Note**: Large file uploads may require adjusting PHP configuration:

```ini
# php.ini
upload_max_filesize = 50M
post_max_size = 50M
```

## Security issues

If you think that you have found a security issue, please do not use the issue tracker and do not post it publicly.
Instead, all security issues must be sent to `security@sylius.com`.
