# Magento_Downloadable

**Magento_Downloadable** module introduces new product type in the Magento application called Downloadable Product.
This module is designed to extend existing functionality of Magento_Catalog module by adding new product type.

The module interacts with the following layout handles:

`view/base/layout` directory:
 The module interacts with the following layout handles:

`view/adminhtml/layout` directory:
  - `catalog_product_downloadable.xml`
  - `catalog_product_simple.xml`
  - `catalog_product_view_type_downloadable.xml`
  - `catalog_product_virtual.xml`
  - `customer_index_wishlist.xml`
  - `downloadable_items.xml`
  - `sales_order_creditmemo_new.xml`
  - `sales_order_creditmemo_updateqty.xml`
  - `sales_order_creditmemo_view.xml`
  - `sales_order_invoice_new.xml`
  - `sales_order_invoice_updateqty.xml`
  - `sales_order_invoice_view.xml`
  - `sales_order_view.xml`
 The module interacts with the following layout handles in the `view/frontend/layout` directory:
  - `catalog_product_view_type_downloadable.xml`
  - `checkout_cart_configure_type_downloadable.xml`
  - `checkout_cart_item_renderers.xml`
  - `checkout_onepage_review_item_renderers.xml`
  - `checkout_onepage_success.xml`
  - `customer_account.xml`
  - `downloadable_customer_products.xml`
  - `multishipping_checkout_success.xml`
  - `sales_email_order_creditmemo_renderers.xml`
  - `sales_email_order_invoice_renderers.xml`
  - `sales_email_order_renderers.xml`
  - `sales_order_creditmemo_renderers.xml`
  - `sales_order_invoice_renderers.xml`
  - `sales_order_item_renderers.xml`
  - `sales_order_print_creditmemo_renderers.xml`
  - `sales_order_print_invoice_renderers.xml`
  - `sales_order_print_renderers.xml`

## Extensibility

Extension developers can interact with the Magento_Csp module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Csp module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.
