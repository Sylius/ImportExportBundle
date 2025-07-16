## Configuration reference

### Export

Minimal resource configuration:

```yaml
sylius_import_export:
    export:
        resources:
            app.brand: ~
```

Full resource configuration:

```yaml
sylius_import_export:
    export:
        resources:
            app.brand:
                serialization_group: 'app:brand:export'
                provider: 'sylius_import_export.provider.resource_data.dbal'
                sections:
                    - 'admin'
                    - 'Sylius\Bundle\ShopBundle\SectionResolver\ShopCustomerAccountSubSection'
```

Reference:

```yaml
sylius_import_export:
    export:
        # The provider used by default when none is configured on a specific resource;
        # defaults to 'sylius_import_export.provider.resource_data.orm'.
        default_provider: <service_id>
        # The section in which the actions are added to the grid.
        # Can be either an FQCN of a class implementing the Sylius\Bundle\CoreBundle\SectionResolver\SectionInterface,
        # or a value of the '_sylius.section' routing attribute;
        # Defaults to 'admin'
        default_section: <string>
        resources:
            # The alias of a resource as configured within the ResourceBundle
            # It can be retried by using the `console/bin sylius:debug:resource` console command.
            <resource_alias>:
                # The group used for data serialization, when not specified, defaults to "sylius_import_export:export"
                serialization_group: <string>
                # Resource specific overwrite of the default_provider
                provider: <service_id>
                # Resource specific overwrite of the default section
                sections:
                    - <string>
                    - ...
                
```

### Import

Minimal resource configuration:

```yaml
sylius_import_export:
    import:
        resources:
            app.brand: ~
```

Full resource configuration:

```yaml
sylius_import_export:
    import:
        resources:
            app.brand:
                validation_groups: ['Default', 'import']
```

Reference:

```yaml
sylius_import_export:
    import:
        # Directory where uploaded import files are stored; defaults to '%kernel.project_dir%/var/import'
        files_directory: <string>
        # Maximum file size for uploads; defaults to '50M'
        file_max_size: <string>
        # Allowed MIME types for import files; defaults to ['application/json']
        allowed_mime_types: [<string>, ...]
        resources:
            # The alias of a resource as configured within the ResourceBundle
            <resource_alias>:
                # Validation groups to use during import validation; defaults to ['Default']
                validation_groups: [<string>, ...]
```
