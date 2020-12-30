# Magento_Translation module

The **Magento_Translation** module enables localization of a store for multiple regions and markets. 

The **Magento_Translation** module provides the inline translation tool.

## Installation details

Before installing this module, note that the **Magento_Translation** is dependent on the following modules:

- Magento_Developer
- Magento_Store
- Magento_Theme

Please find here [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`App/` - the directory that contain Translation class which hold all translation sources and merge them.

For information about a typical file structure of a module in Magento 2, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

### Events

The module dispatches the following events:

 - `adminhtml_cache_flush_system` event in the `\Magento\Translation\Model\Inline\CacheManager::updateAndGetTranslations()` method.

For information about the event system in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/frantend/layout`:
    - `default`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).
