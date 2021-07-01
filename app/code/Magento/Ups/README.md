# Magento_Ups module

The Magento_Ups module implements integration with the United Parcel Service shipping carrier.

## Installation details

Before installing this module, note that the Magento_Ups is dependent on the following modules:

- Magento_Backend
- Magento_CatalogInventory
- Magento_Directory
- Magento_Quote
- Magento_Sales
- Magento_Shipping
- Magento_Store
- Magento_User

Before disabling or uninstalling this module, note the following dependencies:

- Magento_TestModuleUps

For information about enabling or disabling a module, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Ups module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Ups module.

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `adminhtml_system_config_edit`

- `view/frantend/layout`:
    - `checkout_cart_index`
    - `checkout_index_index`

For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).
