# Magento_OfflineShipping module

This module implements the shipping methods which do not involve a direct interaction with shipping carriers, so called offline shipping methods. 
Namely, the following:
- Free Shipping
- Flat Rate
- Table Rates
- Store Pickup

## Installation

Before installing this module, note that the Magento_OfflineShipping is dependent on the following modules:
- `Magento_Store`
- `Magento_Sales`
- `Magento_Quote`
- `Magento_Quote`
- `Magento_SalesRule`

The Magento_OfflineShipping module creates the `shipping_tablerate` table in the database.

This module modifies the following tables in the database:
- `salesrule` - adds column `simple_free_shipping`
- `sales_order_item` - adds column `free_shipping`
- `quote_address` - adds column `free_shipping`
- `quote_item` - adds column `free_shipping`
- `quote_address_item` - adds column `free_shipping`

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_OfflineShipping module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_OfflineShipping module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:
- `checkout_cart_index`
- `checkout_index_index`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

This module extends following ui components located in the `view/adminhtml/ui_component` directory:
- `sales_rule_form`
- `salesrulestaging_update_form`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

## Additional information

You can get more information about offline shipping methods in magento at the articles:
- [How to configure Free Shipping](https://docs.magento.com/user-guide/shipping/shipping-free.html)
- [How to configure Flat Rate](https://docs.magento.com/user-guide/shipping/shipping-flat-rate.html)
- [How to configure Table Rates](https://docs.magento.com/user-guide/shipping/shipping-table-rate.html)
- [How to configure Store Pickup](https://docs.magento.com/user-guide/shipping/shipping-in-store-delivery.html)
