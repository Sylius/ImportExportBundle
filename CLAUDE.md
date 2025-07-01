# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is the Sylius GridImportExportBundle, a Symfony bundle that provides grid import/export functionality for Sylius applications. It supports CSV and JSON export formats and uses Symfony Messenger for asynchronous processing.

## Development Commands

### Testing
- Run tests: `vendor/bin/phpunit`
- Tests are located in `tests/` directory with subdirectories for Integration, Api, and Unit tests

### Code Quality
- Run coding standards check: `vendor/bin/ecs check`
- Fix coding standards: `vendor/bin/ecs check --fix`
- Run static analysis: `vendor/bin/phpstan analyse`

### Dependencies
- Install dependencies: `composer install`
- Update dependencies: `composer update`

## Architecture

### Core Components

**Entity Layer**
- `Process` entity (`src/Entity/Process.php`) - Tracks export processes with UUID, status, format, and output
- Uses Doctrine ORM with XML mapping (`config/doctrine/Process.orm.xml`)

**Export System**
- `ExporterInterface` - Contract for all exporters
- `AbstractExporter` - Base implementation with common functionality
- `CsvExporter` and `JsonExporter` - Format-specific implementations
- `ExporterResolver` - Factory for selecting appropriate exporter

**Data Providers**
- `ResourceDataProviderInterface` - Contract for providing resource data
- `DefaultResourceDataProvider` - Default implementation
- `RequestBasedResourcesIdsProvider` - Provides resource IDs from HTTP requests

**Processing**
- Uses Symfony Messenger for asynchronous processing
- `ExportCommand` - Command message for export jobs
- `ExportCommandHandler` - Handles export processing

**UI Integration**
- Integrates with Sylius Admin UI using Twig hooks
- Custom form types for export configuration
- Grid listeners for adding export actions
- Live Components for dynamic UI updates

### Key Directories
- `src/Controller/` - HTTP endpoints for exports and downloads
- `src/Messenger/` - Async command handling
- `src/Grid/` - Grid integration and export actions
- `src/Form/` - Form types and choice loaders  
- `templates/admin/` - Twig templates for admin interface
- `config/` - Bundle configuration and service definitions
- `assets/` - Frontend JavaScript controllers

### Configuration
- Main config: `config/config.yaml`
- Services: `config/services.xml`
- Routes: `config/routes.yaml` and `config/routes/admin.yaml`
- Grid config: `config/config/sylius_grid.yaml`

## Bundle Structure

This follows standard Symfony bundle conventions:
- Main bundle class: `SyliusGridImportExportBundle`
- DI Extension: `SyliusGridImportExportExtension`
- PSR-4 autoloading with `Sylius\GridImportExport\` namespace
- Uses Sylius coding standards and requires PHP 8.2+