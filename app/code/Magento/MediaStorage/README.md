# Magento_MediaStorage module

This module implements functionality related with upload media files and synchronize it by database.

## Installation

Before installing this module, note that the Magento_MediaStorage is dependent on the following modules:

- `Magento_Catalog`
- `Magento_Theme`

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`App/` - the directory that contains launch application entry point.

For information about a typical file structure of a module in Magento 2, see [Module file structure](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_MediaStorage module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_MediaStorage module.

## Additional information

### Console commands

- `bin/magento catalog:images:resize` - creates resized product images

#### Message Queue Consumer

- `media.storage.catalog.image.resize` - creates resized product images

[Learn how to manage Message Queues](https://devdocs.magento.com/guides/v2.4/config-guide/mq/manage-message-queues.html).

More information can get at articles:
- [Learn how to configure Media Storage Database](https://docs.magento.com/user-guide/system/media-storage-database.html).
- [Learn how to Resize catalog images](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/themes/theme-images.html#resize-catalog-images)
