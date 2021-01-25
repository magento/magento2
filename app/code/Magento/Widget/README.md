# Magento_Widget module

The Magento_Widget module allows Magento application to be extended with custom widget blocks.

## Installation details

Before installing this module, note that the Magento_Widget is dependent on the following modules:

- Magento_Catalog
- Magento_Cms
- Magento_Store

Before disabling or uninstalling this module, note the following dependencies:

- Magento_CatalogWidget
- Magento_CurrencySymbol
- Magento_Newsletter

Refer to [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Widget module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Widget module.

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `adminhtml_widget_index`
    - `adminhtml_widget_instance_block`
    - `adminhtml_widget_instance_edit`
    - `adminhtml_widget_instance_index`
    - `adminhtml_widget_loadoptions`
- `view/frantend/layout`:
    - `default`
    - `print`
    
For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).
