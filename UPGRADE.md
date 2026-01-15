# UPGRADE

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
