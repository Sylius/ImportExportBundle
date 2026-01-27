# UPGRADE

## From v0.2.0 to v0.2.1

### Configuration key serialization_group renamed to serialization_groups

The configuration key for export resources has been renamed to reflect that it can handle multiple groups.

Before:

```yaml
sylius_import_export:
    export:
        resources:
            app.brand:
                serialization_group: 'app:brand:export'
```

After:

```yaml
sylius_import_export:
    export:
        resources:
            app.brand:
                serialization_groups: ['app:brand:export']
```

## From v0.1.x to v0.2.0

### `BatchedExportDataManager` constructor signature changed

The `$processRepository` parameter has been removed from `BatchedExportDataManager::__construct()`.

Before:
```php
public function __construct(
    RepositoryInterface $processRepository,
    string $temporaryDirectory,
)
```

After:
```php
public function __construct(
    string $temporaryDirectory,
)
```

If you extended this class, update your constructor accordingly.
