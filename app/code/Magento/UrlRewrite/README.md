# Magento_UrlRewrite module

The **Magento_UrlRewrite** module provides ability to customize website URLs by creating custom URL rewrite rules.

## Installation details

Before installing this module, note that the **Magento_Ups** is dependent on the following modules:

- Magento_Backend
- Magento_Catalog
- Magento_CatalogUrlRewrite
- Magento_Cms
- Magento_CmsUrlRewrite
- Magento_Store
- Magento_Ui

Before disabling or uninstalling this module, please consider the following dependencies:

- Magento_SampleData

Please find here [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

- `Service/` - directory that contains Data abstract class for url storage.
- `Setup/` - directory that contains `ConvertSerializedDataToJson` class which converts serialized data to Json.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

### Events

The module dispatches the following events:

- `clean_cache_by_tags` event in the `\Magento\UrlRewrite\Model\UrlRewrite::cleanCacheForEntity()` method. Parameters:
    - `object` is a cacheContext object (`\Magento\Framework\Indexer\CacheContext` class).

For information about the event system in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `adminhtml_url_rewrite_index`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a customer form and widgets using the configuration files located in the directories 

- `view/adminhtml/ui_component`:
    - `url_rewrite_listing`

For information about a UI component in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).
