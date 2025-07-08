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
        # defaults to 'sylius_import_export.provider.resource_data.grid'.
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
                # Resource specific overwrite of the default_provider
                provider: <service_id>
                # Resource specific overwrite of the default section
                sections:
                    - <string>
                    - ...
```
