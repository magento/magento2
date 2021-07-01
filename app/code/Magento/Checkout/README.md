# Magento_Checkout module

Magento_Checkout module allows the merchant to register sale transactions with the customer.

Magento_Checkout module implements consumer flow that includes such actions as adding products to cart, providing shipping and billing information, and confirming the purchase.

## Installation details

The Magento_Checkout module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Checkout module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Checkout module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

For more items to extend the checkout see [Customize Checkout](https://devdocs.magento.com/guides/v2.4/howdoi/checkout/checkout_overview.html).

### Events

The module dispatches the following events:

#### Block

- `shortcut_buttons_container` event in the `\Magento\Checkout\Block\QuoteShortcutButtons::_beforeToHtml` method. Parameters:
    - `container` is a `$this` object(`\Magento\Checkout\Block\QuoteShortcutButtons` class).
    - `is_catalog_product` - is catalog product value (`boll` type).
    - `or_position` - or position value (`null` or `string` type).
    - `checkout_session` - checkout session object (`\Magento\Checkout\Model\Session` class).
    - `is_shopping_cart` - is shopping car (`true` value).

#### Controller

- `checkout_onepage_controller_success_action` event in the `\Magento\Checkout\Controller\Onepage\Success::execute` method. Parameters:
    - `order_ids` - last order ID(`int` type).
    - `order` - last order object(`\Magento\Sales\Model\Order` class).

- `checkout_cart_add_product_complete` event in the `\Magento\Checkout\Controller\Cart\Add::execute` method. Parameters:
    - `product` - product was added to cart object(`\Magento\Catalog\Model\Product` class).
    - `request` - request object instance of `\Magento\Framework\App\RequestInterface`.
    - `response` - response object instance of `\Magento\Framework\App\ResponseInterface`.

- `checkout_cart_update_item_complete` event in the `\Magento\Checkout\Controller\Cart\UpdateItemOptions::execute` method. Parameters:
    - `item` - updated quote item object(`\Magento\Quote\Model\Quote\Item` class).
    - `request` - request object instance of `\Magento\Framework\App\RequestInterface`.
    - `response` - response object instance of `\Magento\Framework\App\ResponseInterface`.

- `checkout_controller_onepage_saveOrder` event in the `\Magento\Checkout\Controller\Onepage\SaveOrder::execute` method. Parameters:
    - `result` - result data object(`\Magento\Framework\DataObject` class).
    - `action` is a `$this` object(`\Magento\Checkout\Controller\Onepage\SaveOrder` class).

#### Helper

- `checkout_allow_guest` event in the `\Magento\Checkout\Helper\Data::isAllowedGuestCheckout` method. Parameters:
    - `quote` - quote object(`\Magento\Quote\Model\Quote` class).
    - `store` - store value(`int` type or `\Magento\Store\Model\Store` class).
    - `result` - data object(`\Magento\Framework\DataObject` class).

#### Model

- `custom_quote_process` event in the `\Magento\Checkout\Model\Session::getQuote` method. Parameters:
    - `checkout_session` is a `$this` object(`\Magento\Checkout\Model\Session` class).

- `checkout_quote_init` event in the `\Magento\Checkout\Model\Session::getQuote` method. Parameters:
    - `quote` - quote object(`\Magento\Quote\Model\Quote` class).

- `load_customer_quote_before` event in the `\Magento\Checkout\Model\Session::loadCustomerQuote` method. Parameters:
    - `checkout_session` is a `$this` object(`\Magento\Checkout\Model\Session` class).

- `checkout_quote_destroy` event in the `\Magento\Checkout\Model\Session::clearQuote` method. Parameters:
    - `quote` - quote object(`\Magento\Quote\Model\Quote` class).

- `restoreQuote` event in the `\Magento\Checkout\Model\Session::restoreQuote` method. Parameters:
    - `order` - order object(`\Magento\Sales\Model\Orde` class).
    - `quote` - quote object(`\Magento\Quote\Api\Data\CartInterface` class).

- `checkout_cart_product_add_before` event in the `\Magento\Checkout\Model\Cart::addProduct` method. Parameters:
    - `info` - request info(`\Magento\Framework\DataObject`, class or`int|array` type).
    - `product` - product object(`\Magento\Catalog\Model\Product` class).

- `checkout_cart_product_add_after` event in the `\Magento\Checkout\Model\Cart::addProduct` method. Parameters:
    - `quote_item` - already added item object(`\Magento\Quote\Model\Quote\Item` class).
    - `product` - product object(`\Magento\Catalog\Model\Product` class).

- `checkout_cart_update_items_before` event in the `\Magento\Checkout\Model\Cart::updateItems` method. Parameters:
    - `cart` is a `$this` object(`\Magento\Checkout\Model\Cart` class).
    - `info` - info data object(`\Magento\Framework\DataObject` class).

- `checkout_cart_update_items_after` event in the `\Magento\Checkout\Model\Cart::updateItems` method. Parameters:
    - `cart` is a `$this` object(`\Magento\Checkout\Model\Cart` class).
    - `info` - info data object(`\Magento\Framework\DataObject` class).

- `checkout_cart_save_before` event in the `\Magento\Checkout\Model\Cart::save` method. Parameters:
    - `cart` is a `$this` object(`\Magento\Checkout\Model\Cart` class).

- `checkout_cart_save_after` event in the `\Magento\Checkout\Model\Cart::save` method. Parameters:
    - `cart` is a `$this` object(`\Magento\Checkout\Model\Cart` class).

- `checkout_cart_product_update_after` event in the `\Magento\Checkout\Model\Cart::updateItem` method. Parameters:
    - `quote_item` - already updated item (`\Magento\Quote\Model\Quote\Item` class).
    - `product` - product object(`\Magento\Catalog\Model\Product` class).

- `restore_quote` event in the `\Magento\Checkout\Model\Session::restoreQuote` method. Parameters:
    - `order` - last real order object(`\Magento\Sales\Model\Order` class).
    - `quote` - active cart object(`\Magento\Quote\Api\Data\CartInterface` class).

- `checkout_type_onepage_save_order_after` event in the `\Magento\Checkout\Model\Type\Onepage::saveOrder` method. Parameters:
    - `order` - order object(`\Magento\Sales\Model\Order` class).
    - `quote` - current quote object(`\Magento\Quote\Model\Quote` class).

- `checkout_submit_all_after` event in the `\Magento\Checkout\Model\Type\Onepage::saveOrder` method. Parameters:
    - `order` - order object(`\Magento\Sales\Model\Order` class).
    - `quote` - current quote object(`\Magento\Quote\Model\Quote` class).

For information about an event in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

The module introduces layout handles in the `view/frontend/layout` directory.

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### Public APIs

`\Magento\Checkout\Api\AgreementsValidatorInterface`:
- check is valid agreements by ID list

`app/code/Magento/Checkout/Api/GuestPaymentInformationManagementInterface.php:14`:

- set payment information and place order for a specified cart
- set payment information for a specified cart
- get payment information by the cart ID

`\Magento\Checkout\Api\GuestShippingInformationManagementInterface`:

- save address information

`\Magento\Checkout\Api\GuestTotalsInformationManagementInterface`:

- calculate quote totals based on address and shipping method

`\Magento\Checkout\Api\PaymentInformationManagementInterface`:

- set payment information and place order for a specified cart
- set payment information for a specified cart
- get payment information

`PaymentProcessingRateLimiterInterface`:

- limit an attempt to initiate a new payment processing

`\Magento\Checkout\Api\ShippingInformationManagementInterface`:

- save shipping address information

`\Magento\Checkout\Api\TotalsInformationManagementInterface`:

- calculate quote totals based on address and shipping method

For information about a public API in Magento 2, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).
