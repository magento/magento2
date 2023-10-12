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

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_OfflineShipping module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_OfflineShipping module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Layouts

This module introduces the following layouts in the `view/frontend/layout` directory:

- `checkout_cart_index`
- `checkout_index_index`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

This module extends following ui components located in the `view/adminhtml/ui_component` directory:

- `sales_rule_form`
- `salesrulestaging_update_form`

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

## Additional information

You can get more information about offline shipping methods in magento at the articles:

- [How to configure Free Shipping](https://experienceleague.adobe.com/docs/commerce-admin/stores-sales/delivery/basic-methods/shipping-free.html)
- [How to configure Flat Rate](https://experienceleague.adobe.com/docs/commerce-admin/stores-sales/delivery/basic-methods/shipping-flat-rate.html)
- [How to configure Table Rates](https://experienceleague.adobe.com/docs/commerce-admin/stores-sales/delivery/basic-methods/shipping-table-rate.html)
- [How to configure Store Pickup](https://experienceleague.adobe.com/docs/commerce-admin/stores-sales/delivery/basic-methods/shipping-in-store-delivery.html)
