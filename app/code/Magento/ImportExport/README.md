# Magento_ImportExport module

This module provides a framework and basic functionality for importing/exporting various entities in Magento.
It can be disabled and in such case all dependent import/export functionality (products, customers, orders etc.) will be disabled in Magento.

## Installation

The Magento_ImportExport module creates the following tables in the database:

- `importexport_importdata`
- `import_history`

All database schema changes made by this module are rolled back when the module gets disabled and setup:upgrade command is run.

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`Files/` - the directory that contains sample import files.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_ImportExport module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_ImportExport module.

### Layouts

This module introduces the following layout handles in the `view/frontend/layout` directory:

- `adminhtml_export_getfilter`
- `adminhtml_export_index`
- `adminhtml_history_grid_block`
- `adminhtml_history_index`
- `adminhtml_import_busy`
- `adminhtml_import_index`
- `adminhtml_import_start`
- `adminhtml_import_validate`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

You can extend an export updates using the configuration files located in the `view/adminhtml/ui_component` directory:

- `export_grid`

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

### Public APIs

- `Magento\ImportExport\Api\Data\ExportInfoInterface`
    - getter and setter interface with data needed for export

- `Magento\ImportExport\Api\Data\ExtendedExportInfoInterface`
    - extends `Magento\ImportExport\Api\Data\ExportInfoInterface`. Contains data for skipped attributes

- `\Magento\ImportExport\Api\ExportManagementInterface`
    - Executing actual export and returns export data

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

## Additional information

#### Message Queue Consumer

- `exportProcessor` - consumer to run export process

[Learn how to manage Message Queues](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues.html).

#### Create custom import entity

1. Declare the new import entity in `etc/import.xml`
2. Create an import model

#### Create custom export entity

1. Declare the new import entity in `etc/export.xml`
2. Create an export model

You can get more information about import/export processes in magento at the articles:

- [Create custom import entity](https://developer.adobe.com/commerce/php/tutorials/backend/create-custom-import-entity/)
- [Import](https://experienceleague.adobe.com/docs/commerce-admin/systems/data-transfer/import/data-import.html)
- [Export](https://experienceleague.adobe.com/docs/commerce-admin/systems/data-transfer/data-export.html)
