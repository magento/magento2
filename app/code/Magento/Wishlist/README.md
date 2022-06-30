# Magento_Wishlist module

The Magento_Wishlist module implements the Wishlist functionality.

This module allows customers to create a list of products that they can add to their shopping cart to be purchased at a later date, or share with friends.

## Installation details

Before installing this module, note that the Magento_Wishlist is dependent on the following modules:

- Magento_Captcha
- Magento_Catalog
- Magento_Customer

Before disabling or uninstalling this module, note the following dependencies:

- Magento_WishlistAnalytics

Refer to [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Pricing/` - the directory that contain solutions for configurable and downloadable product price.

For information about a typical file structure of a module, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Wishlist module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Wishlist module.

### Events

The module dispatches the following events:

- `product_option_renderer_init` event in the `\Magento\Wishlist\Block\Customer\Wishlist\Item\Options::_construct()` method. Parameters:
    - `block` is a Wishlist block customer items (`\Magento\Wishlist\Block\Customer\Wishlist\Item\Options` class).
- `rss_wishlist_xml_callback` event in the `\Magento\Wishlist\Model\Rss\Wishlist::getRssData()` method. Parameters:
    - `$args` is a array of product object (`\Magento\Catalog\Model\Product` class).
- `wishlist_add_item` event in the `\Magento\Wishlist\Model\Wishlist::addItem()` method. Parameters:
    - `item` is an item object (`\Magento\Wishlist\Model\Item` class).
- `wishlist_add_product` event in the `\Magento\Wishlist\Controller\Index\Add::execute()` method. Parameters:
    - `wishlist` is a Wishlist object (`\Magento\Wishlist\Model\Wishlist` class).
    - `product` is a product object (`\Magento\Catalog\Api\Data\ProductInterface` class).
    - `item` is an item object (`\Magento\Wishlist\Model\Item` class).
- `wishlist_item_collection_products_after_load` event in the `\Magento\Wishlist\Model\ResourceModel\Item\Collection::_assignProducts()` method. Parameters:
    - `product_collection` is a product collection object (`\Magento\Catalog\Model\ResourceModel\Product\Collection` class).
- `wishlist_items_renewed` event in the `\Magento\Wishlist\Helper\Data::calculate()` method.
- `wishlist_product_add_after` event in the `\Magento\Wishlist\Model\Wishlist::addNewItem()` method. Parameters:
    - `items` is an array of item object (`\Magento\Wishlist\Model\Item` class).
- `wishlist_share` event in the `\Magento\Wishlist\Controller\Index\Send::execute()` method. Parameters:
    - `wishlist` is a Wishlist object (`\Magento\Wishlist\Model\Wishlist` class).
- `wishlist_update_item` event in the `\Magento\Wishlist\Controller\Index\UpdateItemOptions::execute()` method. Parameters:
    - `wishlist` is a Wishlist object (`\Magento\Wishlist\Model\Wishlist` class).
    - `product` is a product object (`\Magento\Catalog\Api\Data\ProductInterface` class).
    - `item` is an item object (`\Magento\Wishlist\Model\Item` class).

For information about the event, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `customer_index_wishlist`
- `view/base/layout`:
    - `catalog_product_prices`
- `view/frantend/layout`:
    - `catalog_category_view`
    - `catalog_product_view`
    - `catalogsearch_advanced_result`
    - `checkout_cart_index`
    - `checkout_cart_item_renderers`
    - `customer_account`
    - `default`
    - `wishlist_email_items`
    - `wishlist_email_rss`
    - `wishlist_index_configure`
    - `wishlist_index_configure_type_bundle`
    - `wishlist_index_configure_type_configurable`
    - `wishlist_index_configure_type_downloadable`
    - `wishlist_index_configure_type_grouped`
    - `wishlist_index_configure_type_simple`
    - `wishlist_index_index`
    - `wishlist_index_share`
    - `wishlist_shared_index.xml`
    
For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a customer form and widgets using the configuration files located in the directories 
- `view/base/ui_component`:
    - `customer_form`
- `view/frontend/ui_component`:
    - `widget_recently_compared`
    - `widget_recently_viewed`

For information about a UI component, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).
