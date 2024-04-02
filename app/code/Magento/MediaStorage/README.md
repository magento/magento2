# Magento_MediaStorage module

This module implements functionality related with upload media files and synchronize it by database.

## Installation

Before installing this module, note that the Magento_MediaStorage is dependent on the following modules:

- `Magento_Catalog`
- `Magento_Theme`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`App/` - the directory that contains launch application entry point.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_MediaStorage module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_MediaStorage module.

## Additional information

### Console commands

- `bin/magento catalog:images:resize` - creates resized product images

#### Message Queue Consumer

- `media.storage.catalog.image.resize` - creates resized product images

[Learn how to manage Message Queues](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues.html).

More information can get at articles:

- [Learn how to configure Media Storage Database](https://experienceleague.adobe.com/docs/commerce-admin/content-design/media/storage/media-storage-database.html).
- [Learn how to Resize catalog images](https://developer.adobe.com/commerce/frontend-core/guide/themes/configure/#resize-catalog-images)
