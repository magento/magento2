# Magento_MediaStorage module

This module implements functionality related with uploading media files and synchronizing them with the database.

## Installation

Before installing this module, note that the Magento_MediaStorage module is dependent on the following modules:

- `Magento_Catalog`
- `Magento_Theme`

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Structure

`App/` - the directory that contains launch application entry point.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_MediaStorage module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_MediaStorage module.

## Additional information

### Console commands

- `bin/magento catalog:images:resize` - creates resized product images.

#### Message Queue Consumer

- `media.storage.catalog.image.resize` - creates resized product images.

[Learn how to manage Message Queues](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/message-queues/manage-message-queues).

More information can get at articles:

- [Learn how to configure Media Storage Database](https://experienceleague.adobe.com/en/docs/commerce-admin/content-design/wysiwyg/storage/media-storage-database).
- [Learn how to Resize catalog images](https://developer.adobe.com/commerce/frontend-core/guide/themes/configure/#resize-catalog-images)
