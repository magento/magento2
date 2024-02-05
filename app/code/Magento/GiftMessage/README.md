# Magento_GiftMessage module

This module allows to add a message to order or to each ordered item either on frontend or backend.

## Installation

Before installing this module, note that the Magento_GiftMessage is dependent on the following modules:

- `Magento_Catalog`
- `Magento_Sales`
- `Magento_Quote`

Before disabling or uninstalling this module, note that the Magento_GiftMessageGraphQl module depends on this module

The Magento_GiftMessage module creates the `gift_message` table in the database.

This module modifies the following tables in the database:

- `quote` - adds column `gift_message_id`
- `quote_address` - adds column `gift_message_id`
- `quote_item` - adds column `gift_message_id`
- `quote_address_item` - adds column `gift_message_id`
- `sales_order` - adds column `gift_message_id`
- `sales_order_item` - adds columns `gift_message_id` and `gift_message_available`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_GiftMessage module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_GiftMessage module.

A lot of functionality in the module is on JavaScript, use [mixins](https://developer.adobe.com/commerce/frontend-core/javascript/mixins/) to extend it.

### Events

The module dispatches the following events:

- `gift_options_prepare_items` event in the `\Magento\GiftMessage\Block\Message\Inline::getItems` method. Parameters:
    - `items` is a entityItems (`array` type)

- `gift_options_prepare` event in the `\Magento\GiftMessage\Block\Message\Inline::isMessagesOrderAvailable` method. Parameters:
    - `entity` is an entity object

For information about an event in Magento 2, see [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/#events).

### Layout

This module introduces the following layouts in the `view/frontend/layout` and `view/adminhtml/layout` directories:

- `view/adminhtml/layout`:
    - `sales_order_create_index`
    - `sales_order_create_load_block_data`
    - `sales_order_create_load_block_items`
    - `sales_order_view`
- `view/frontend/layout`:
    - `checkout_cart_index`
    - `checkout_cart_item_renderers`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### Public APIs

#### Data

- `Magento\GiftMessage\Api\Data\MessageInterface`
    - gift message data

#### Cart

- `\Magento\GiftMessage\Api\CartRepositoryInterface`
    - get the gift message by cart ID for specified shopping cart
    - set the gift message for an entire shopping cart

- `\Magento\GiftMessage\Api\GuestCartRepositoryInterface`
    - get the gift message by cart ID for specified shopping cart
    - set the gift message for an entire shopping cart

#### Cart Item

- `\Magento\GiftMessage\Api\GuestItemRepositoryInterface`
    - get the gift message for a specified item in a specified shopping cart
    - set the gift message for a specified item in a specified shopping cart

- `\Magento\GiftMessage\Api\ItemRepositoryInterface`
    - get the gift message for a specified item in a specified shopping cart
    - set the gift message for a specified item in a specified shopping cart

#### Order

- `\Magento\GiftMessage\Api\OrderItemRepositoryInterface`
    - get the gift message for a specified order
    - set the gift message for an entire order

#### Order Item

- `\Magento\GiftMessage\Api\OrderItemRepositoryInterface`
    - get the gift message for a specified item in a specified order
    - set the gift message for a specified item in a specified order

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

## Additional information

[Learn more about Gift Options and Gift Message](https://experienceleague.adobe.com/docs/commerce-admin/stores-sales/point-of-purchase/cart/cart-configuration.html#gift-options).
