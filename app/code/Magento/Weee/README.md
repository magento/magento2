# Magento_Weee module

The Magento_Weee module enables the application of fees/fixed product taxes (FPT) on certain types of products, usually related to electronic devices and recycling.

Fixed product taxes can be used to setup a WEEE tax that is a fixed amount, rather than a percentage of the product price. FPT can be configured to be displayed at various places in Magento. Rules, amounts, and display options can be configured in the backend.

This module extends the existing functionality of Magento_Tax.

The Magento_Weee module includes the following:

- Ability to add different number of fixed product taxes to product. They are treated as a product attribute.
- Configuration of where WEEE appears (on category, product, sales, invoice, or credit memo pages) and whether FPT should be taxed.
- A new line item in the totals section.

## Installation details

The Magento_Weee module can be installed automatically (using native Magento install mechanism) without any additional actions.

Before installing this module, note that the Magento_Weee is dependent on the following modules:

- Magento_Catalog
- Magento_Checkout
- Magento_Customer
- Magento_Quote
- Magento_Sales
- Magento_Store
- Magento_Tax

Refer to [how to enable or disable modules in Magento 2](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`Pricing/` - directory that contain tax adjustment.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Weee module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Weee module.

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `catalog_product_form`
    - `sales_creditmemo_item_price`
    - `sales_invoice_item_price`
    - `sales_order_create_item_price`
    - `sales_order_creditmemo_new`
    - `sales_order_creditmemo_updateqty`
    - `sales_order_creditmemo_view`
    - `sales_order_invoice_new`
    - `sales_order_invoice_updateqty`
    - `sales_order_invoice_view`
    - `sales_order_item_price`
    - `sales_order_view`

- `view/base/layout`:
    - `catalog_product_prices`

- `view/frontend/layout`:
    - `checkout_cart_index`
    - `checkout_index_index`
    - `checkout_item_price_renderers`
    - `default`
    - `sales_email_item_price`
    - `sales_email_order_creditmemo_items`
    - `sales_email_order_invoice_items`
    - `sales_email_order_items`
    - `sales_guest_creditmemo`
    - `sales_guest_invoice`
    - `sales_guest_print`
    - `sales_guest_printcreditmemo`
    - `sales_guest_printinvoice`
    - `sales_guest_view`
    - `sales_order_creditmemo`
    - `sales_order_invoice`
    - `sales_order_item_price`
    - `sales_order_print`
    - `sales_order_printcreditmemo`
    - `sales_order_printinvoice`
    - `sales_order_view`

For more information about a layout, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

You can extend a customer form and widgets using the configuration files located in the directories

- `view/adminhtml/ui_component`:
    - `product_attribute_add_form`
- `view/frontend/ui_component`:
    - `widget_recently_compared`
    - `widget_recently_viewed`

For information about a UI component, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).
