# Magento_Bundle module
Magento_Bundle module introduces new product type in the Magento application named Bundle Product.

This module is designed to extend existing functionality of Magento_Catalog module by adding new product type.

## Installation details

Before disabling or uninstalling this module, note that the following modules depend on this module:

- Magento_BundleGraphQl
- Magento_BundleImportExport
- Magento_Wishlist

For information about a module enabling or disabling in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Pricing/` - the directory that contains solutions for bundle product price.

For information about a typical file structure of a module in Magento 2, see [Module file structure](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Bundle module. For more information about the Magento extension mechanism, see [Magento plug-ins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Bundle module.

### Events

The module dispatches the following events:

 - `catalog_product_option_price_configuration_after` in the `\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle::getJsonConfig()` method. Parameters:
    - `configObj` is a config data object will be returned(`\Magento\Framework\DataObject` class).
- `catalog_product_get_final_price` in the methods `\Magento\Bundle\Pricing\Price\BundleSelectionPrice::getValue()`, `\Magento\Bundle\Model\Product\Price::getSelectionFinalTotalPrice()`, `\Magento\Bundle\Model\Product\Price::getFinalPrice`. 
  
    Parameters:
    - `product` - a product with the final price set (`\Magento\Catalog\Model\Product` class).
    - `qty` - a qty of product(`int` type).
- `prepare_catalog_product_collection_prices` in the `\Magento\Bundle\Pricing\Price\BundleSelectionPrice::getValue()` method. Parameters:
    - `collection` - bundle collection of selection before price calculation (`\Magento\Bundle\Model\ResourceModel\Selection\Collection` class).
    - `store_id` - bundle product store ID(`int` type).
- `catalog_product_prepare_index_select` in the `\Magento\Bundle\Model\ResourceModel\Indexer\Price::prepareBundlePriceByType` method. Parameters:
    - `select` - select for bundle price by type to DB(`\Magento\Framework\DB\Select` class)
    - `entity_field` - entity ID column(`\Zend_Db_Expr` class)
    - `website_field` - website ID column(`\Zend_Db_Expr` class)
    - `store_field` - default store ID column(`\Zend_Db_Expr` class)

For information about the event system in Magento 2, see [Events and observers](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:
- `view/adminhtml/layout`:
    - `adminhtml_order_shipment_new`
    - `adminhtml_order_shipment_view`
    - `catalog_product_bundle`
    - `catalog_product_new`
    - `catalog_product_view_type_bundle`
    - `customer_index_wishlist`
    - `sales_order_creditmemo_new`
    - `sales_order_creditmemo_updateqty`
    - `sales_order_creditmemo_view`
    - `sales_order_invoice_new`
    - `sales_order_invoice_updateqty`
    - `sales_order_invoice_view`
    - `sales_order_invoice_view`
- `view/base/layout`:
  - `catalog_product_prices`
- `view/frantend/layout`:
    - `catalog_product_view_type_bundle`
    - `catalog_product_view_type_simple`
    - `checkout_cart_configure_type_bundle`
    - `checkout_cart_item_renderers`
    - `checkout_onepage_review_item_renderers`
    - `default`
    - `sales_email_order_creditmemo_renderers`
    - `sales_email_order_invoice_renderers`
    - `sales_email_order_renderers`
    - `sales_email_order_shipment_renderers`
    - `sales_order_creditmemo_renderers`
    - `sales_order_invoice_renderers`
    - `sales_order_item_renderers`
    - `sales_order_print_creditmemo_renderers`
    - `sales_order_print_invoice_renderers`
    - `sales_order_print_renderers`
    - `sales_order_print_shipment_renderers`
    - `sales_order_shipment_renderers`

For more information about a layout in Magento 2, see the [Layout documentation](http://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a product and widgets updates using the configuration files located in the directories 
- `view/adminhtml/ui_component`:
    - 'bundle_product_listing'
- `view/frontend/ui_component`:
    - 'widget_recently_compared'
    - 'widget_recently_viewed'

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

`\Magento\Bundle\Api\ProductLinkManagementInterface`:

- get all children for bundle product.
- add a child to bundle product to specified Bundle option by the product SKU or product.
- save bundle child.
- remove bundle child.

`\Magento\Bundle\Api\ProductOptionRepositoryInterface`:

- get option for bundle product.
- get all options list for bundle product.
- remove bundle option by ID.
- add new option to bundle product.

`\Magento\Bundle\Api\ProductOptionTypeListInterface`:
- get all types for options for bundle products.

`\Magento\Bundle\Api\ProductOptionManagementInterface`:
- add new option for bundle product.

For information about a public API in Magento 2, see [Public interfaces & APIs](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).

## Additional information


For more information about creating product, see [Creating Bundle Product](https://docs.magento.com/user-guide/catalog/product-create-bundle.html)
