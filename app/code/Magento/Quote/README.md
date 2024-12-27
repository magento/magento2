# Magento_Quote module

This module provides customer cart management functionality.

## Installation

The Magento_Quote module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

The Magento_Quote module creates the following table in the database:

- `quote`
- `quote_address`
- `quote_item`
- `quote_address_item`
- `quote_item_option`
- `quote_payment`
- `quote_shipping_rate`
- `quote_id_mask`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_Quote module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Quote module.

### Events

The module dispatches the following events:

- `sales_quote_address_collection_load_after` event in the `\Magento\Quote\Model\ResourceModel\Quote\Address\Collection::_afterLoad` method. Parameters:
    - `quote_address_collection` is a `$this` object (`Magento\Quote\Model\ResourceModel\Quote\Address\Collection` class)

- `items_additional_data` event in the `\Magento\Quote\Model\Cart\Totals\ItemConverter::modelToDataObject` method. Parameters:
    - `item` is a quote_item object (`\Magento\Quote\Model\Quote\Item` class)

- `sales_quote_remove_item` event in the `\Magento\Quote\Model\Quote::removeItem` method. Parameters:
    - `quote_item` is a quote_item object (`\Magento\Quote\Model\Quote\Item` class)

- `sales_quote_add_item` event in the `\Magento\Quote\Model\Quote::addItem` method. Parameters:
    - `quote_item` is a quote_item object (`\Magento\Quote\Model\Quote\Item` class)

- `sales_quote_product_add_after` event in the `\Magento\Quote\Model\Quote::addProduct` method. Parameters:
    - `items` is an array with quot_item objects (`\Magento\Quote\Model\Quote\Item` class)

- `sales_quote_merge_before` event in the `\Magento\Quote\Model\Quote::merge` method. Parameters:
    - `quote` is a `$this` object (`\Magento\Quote\Model\Quote` class)
    - `source` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_quote_merge_after` event in the `\Magento\Quote\Model\Quote::merge` method. Parameters:
    - `quote` is a `$this` object (`\Magento\Quote\Model\Quote` class)
    - `source` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_convert_quote_to_order` event in the `\Magento\Quote\Model\Quote\Address\ToOrder::convert` method. Parameters:
    - `order` is an order object (`\Magento\Sales\Model\Order` class)
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_quote_item_qty_set_after` event in the `\Magento\Quote\Model\Quote\Item::setQty` method. Parameters:
    - `item` is a `$this` object (`\Magento\Quote\Model\Quote\Item` class)

- `sales_quote_item_set_product` event in the `\Magento\Quote\Model\Quote\Item::setProduct` method. Parameters:
    - `product` is a product object (`\Magento\Catalog\Model\Product` class)
    - `quote_item` is a `$this` object (`\Magento\Quote\Model\Quote\Item` class)

- `sales_quote_payment_import_data_before` event in the `\Magento\Quote\Model\Quote\Payment::importData` method. Parameters:
    - `payment` is a `$this` object (`\Magento\Quote\Model\Quote\Payment` class)
    - `input` is a data object (`\Magento\Framework\DataObject` class)

- `sales_quote_collect_totals_before` event in the `\Magento\Quote\Model\Quote\TotalsCollector::collect` method. Parameters:
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_quote_collect_totals_after` event in the `\Magento\Quote\Model\Quote\TotalsCollector::collect` method. Parameters:
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_quote_address_collect_totals_before` event in the `\Magento\Quote\Model\Quote\TotalsCollector::collectAddressTotals` method. Parameters:
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)
    - `shipping_assignment` is a shipping_assignment object (`\Magento\Quote\Model\ShippingAssignment` class)
    - `total` is a total object (`\Magento\Quote\Model\Quote\Address\Total` class)

- `sales_quote_address_collect_totals_after` event in the `\Magento\Quote\Model\Quote\TotalsCollector::collectAddressTotals` method. Parameters:
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)
    - `shipping_assignment` is a shipping_assignment object (`\Magento\Quote\Model\ShippingAssignment` class)
    - `total` is a total object (`\Magento\Quote\Model\Quote\Address\Total` class)

- `checkout_submit_before` event in the `\Magento\Quote\Model\QuoteManagement::placeOrder` method. Parameters:
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)

- `checkout_submit_all_after` event in the `\Magento\Quote\Model\QuoteManagement::placeOrder` method. Parameters:
    - `order` is an order object (`\Magento\Sales\Model\Order` class)
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_model_service_quote_submit_before` event in the `\Magento\Quote\Model\QuoteManagement::submitQuote` method. Parameters:
    - `order` is an order object (`\Magento\Sales\Model\Order` class)
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_model_service_quote_submit_success` event in the `\Magento\Quote\Model\QuoteManagement::submitQuote` method. Parameters:
    - `order` is an order object (`\Magento\Sales\Model\Order` class)
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)

- `sales_model_service_quote_submit_failure` event in the `\Magento\Quote\Model\QuoteManagement::rollbackAddresses` method. Parameters:
    - `order` is an order object (`\Magento\Sales\Model\Order` class)
    - `quote` is a quote object (`\Magento\Quote\Model\Quote` class)
    - `exception` is an exception object (`\Exception` class)

- `prepare_catalog_product_collection_prices` event in the `\Magento\Quote\Model\ResourceModel\Quote\Item\Collection::_assignProducts` method. Parameters:
    - `collection` is a product collection object (`\Magento\Quote\Model\ResourceModel\Quote\Item\Collection` class)
    - `store_id` is a store ID (`int` type)

- `sales_quote_item_collection_products_after_load` event in the `\Magento\Quote\Model\QuoteManagement::_assignProducts` method. Parameters:
    - `collection` is a product collection object (`\Magento\Catalog\Model\ResourceModel\Product\Collection` class)

For information about an event in Magento 2, see [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/#events).

### Public APIs

#### Data

- `\Magento\Quote\Api\Data\AddressAdditionalDataInterface`
    - provides additional data with quote address information

- `\Magento\Quote\Api\Data\AddressInterface`
    - quote address data

- `\Magento\Quote\Api\Data\CartInterface`
    - quote data

- `\Magento\Quote\Api\Data\CartItemInterface`
    - quote item data
  
- `\Magento\Quote\Api\Data\CartSearchResultsInterfac`
    - quote search result data

- `\Magento\Quote\Api\Data\CurrencyInterface`
    - currency data
  
- `\Magento\Quote\Api\Data\EstimateAddressInterface`
    - estimate address data
  
- `\Magento\Quote\Api\Data\PaymentInterface`
    - payment data
  
- `\Magento\Quote\Api\Data\PaymentMethodInterface`
    - payment method data
  
- `\Magento\Quote\Api\Data\ProductOptionInterface`
    - product option data
  
- `\Magento\Quote\Api\Data\ShippingAssignmentInterface`
    - shipping assigment data

- `\Magento\Quote\Api\Data\ShippingInterface`
    - shipping data

- `\Magento\Quote\Api\Data\ShippingMethodInterface`
    - shipping method data
  
- `\Magento\Quote\Api\Data\TotalsAdditionalDataInterface`
    - provides additional data for totals collection

- `\Magento\Quote\Api\Data\TotalSegmentInterface`
    - total segment data
  
- `\Magento\Quote\Api\Data\TotalsInterfacee`
    - quote totals data
  
- `\Magento\Quote\Api\Data\TotalsItemInterface`
    - quote items totals data

#### General

- `\Magento\Quote\Api\ChangeQuoteControlInterface`
    - checks if user is allowed to change the quote

#### Guest

- `\Magento\Quote\Api\GuestBillingAddressManagementInterface`
    - assigns a specified billing address to a specified quote
    - gets the billing address for a specified quote

- `\Magento\Quote\Api\GuestCartItemRepositoryInterface`
    - gets lists items that are assigned to a specified quote
    - add/update the specified cart guest item
    - removes the specified item from the specified quote

- `\Magento\Quote\Api\GuestCouponManagementInterface`
    - gets coupon for a specified quote by quote ID
    - adds a coupon by code to a specified quote
    - deletes a coupon from a specified quote by quote ID

- `\Magento\Quote\Api\GuestCartManagementInterface`
    - gets list items that are assigned to a specified quote
    - add/update the specified quote item
    - deletes the specified item from the specified quote

- `\Magento\Quote\Api\GuestPaymentMethodManagementInterface`
    - adds a specified payment method to a specified shopping quote
    - gets the payment method for a specified shopping quote
    - gets list available payment methods for a specified shopping quote

- `\Magento\Quote\Api\GuestShipmentEstimationInterface`
    - estimates shipping by address and return list of available shipping methods

- `\Magento\Quote\Api\GuestShippingMethodManagementInterface`
    - gets list applicable shipping methods for a specified quote
    - estimates shipping

- `\Magento\Quote\Api\GuestCartRepositoryInterface`
    - gets quote by quote ID for guest user

- `\Magento\Quote\Api\GuestCartTotalManagementInterface`
    - sets shipping/billing methods and additional data for a quote and collect totals for guest

- `\Magento\Quote\Api\GuestCartTotalRepositoryInterface`
    - gets quote totals by quote ID for guest user

- `\Magento\Quote\Model\GuestCart\GuestShippingAddressManagementInterface`
    - assign a specified shipping address to a specified quote
    - gets the shipping address for a specified quote

- `\Magento\Quote\Model\GuestCart\GuestShippingMethodManagementInterface`
    - sets the carrier and shipping methods codes for a specified quote
    - gets the selected shipping method for a specified quote

#### Registered customer

- `\Magento\Quote\Api\BillingAddressManagementInterface`
    - assigns a specified billing address to a specified quote
    - gets the billing address for a specified quote

- `\Magento\Quote\Api\CartItemRepositoryInterface`
    - gets lists items that are assigned to a specified quote
    - add/update the specified quote item
    - removes the specified item from the specified quote

- `\Magento\Quote\Api\CartManagementInterface`
    - creates an empty quote and quote for a guest
    - creates an empty quote and quote for a specified customer if customer does not have a quote yet
    - returns information for the quote for a specified customer
    - assigns a specified customer to a specified shopping quote
    - places an order for a specified quote

- `\Magento\Quote\Api\CartRepositoryInterface`
    - gets quote by quote ID
    - gets list carts that match specified search criteria
    - gets quote by customer ID
    - gets active quote by quote ID
    - gets active quote by customer ID
    - saves quote
    - deletes quote
  
- `\Magento\Quote\Api\CartTotalManagementInterface`
    - sets shipping/billing methods and additional data for quote and collect totals

- `\Magento\Quote\Api\CartTotalRepositoryInterface`
    - gets quote totals by quote ID

- `\Magento\Quote\Api\CouponManagementInterface`
    - gets coupon for a specified quote by quote ID
    - adds a coupon by code to a specified quote
    - deletes a coupon from a specified quote by quote ID

- `\Magento\Quote\Api\PaymentMethodManagementInterface`
    - adds a specified payment method to a specified shopping quote
    - gets the payment method for a specified shopping quote
    - gets list available payment methods for a specified shopping quote

- `\Magento\Quote\Api\ShipmentEstimationInterface`
    - estimates shipping by address and return list of available shipping methods

- `\Magento\Quote\Api\ShippingMethodManagementInterface`
    - estimates shipping by quote ID an Address
    - estimates shipping by quote ID an address ID
    - get lists applicable shipping methods for a specified quote

- `\Magento\Quote\Model\ShippingAddressManagementInterface`
    - assigns a specified shipping address to a specified quote
    - gets the shipping address for a specified quote

- `\Magento\Quote\Model\ShippingMethodManagementInterface`
    - sets the carrier and shipping methods codes for a specified quote
    - gets the selected shipping method for a specified quote

#### Model

- `\Magento\Quote\Model\Quote\Address\FreeShippingInterface`
    - checks if is a free shipping

- `\Magento\Quote\Model\Quote\Address\RateCollectorInterface`
    - retrieves all methods for supplied shipping data

- `\Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface`
    - converts masked quote ID to the quote ID (entity ID)

- `\Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface`
    - converts quote ID to the masked quote ID

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).
