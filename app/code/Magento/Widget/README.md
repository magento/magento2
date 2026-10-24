# Magento_Widget module

This module allows Magento application to be extended with custom widget blocks.

## Installation details

Before installing this module, note that this module is dependent on the following modules:

- Magento_Catalog
- Magento_Cms
- Magento_Store

Before disabling or uninstalling this module, note the following dependencies:

- Magento_CatalogWidget
- Magento_CurrencySymbol
- Magento_Newsletter

Refer to [how to enable or disable modules in Magento 2](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.

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

For more information about a layout, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).
