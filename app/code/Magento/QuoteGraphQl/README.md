# Magento_QuoteGraphQl module

This module provides type and resolver information for the GraphQL module to generate quote (cart) information endpoints.
It also provides endpoints for modifying a quote.

## Installation

Before installing this module, note that it is dependent on the following modules:

- `Magento_CatalogGraphQl`

This module does not introduce any database schema modifications or new data.

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with the Magento_QuoteDownloadableLinks module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_QuoteDownloadableLinks module.

## Additional information

You can get more information about GraphQL in the [Adobe Developer documentation](https://developer.adobe.com/commerce/webapi/graphql/).

### GraphQL Query

- `cart` query - retrieve information about a particular cart.
  [Learn more about cart query](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/queries/cart/).
  
- `customerCart` query - returns the active cart for the logged-in customer. If the cart does not exist, the query creates one.
  [Learn more about customerCart query](https://developer.adobe.com/commerce/webapi/graphql/schema/customer/queries/cart/).

### GraphQL Mutation

- `createEmptyCart` mutation - creates an empty shopping cart for a guest or logged-in customer.
  [Learn more about createEmptyCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/create-empty-cart/).

- `addSimpleProductsToCart` mutation - allows you to add any number of simple and group products to the cart at the same time.
  [Learn more about addSimpleProductsToCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/add-simple-products/).

- `addVirtualProductsToCart` mutation - allows you to add multiple virtual products to the cart at the same time, but you cannot add other product types with this mutation.
  [Learn more about addVirtualProductsToCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/add-virtual-products/).

- `applyCouponToCart` mutation - applies a pre-defined coupon code to the specified cart.
  [Learn more about applyCouponToCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/apply-coupon/).

- `removeCouponFromCart` mutation - removes a previously-applied coupon from the cart.
  [Learn more about removeCouponFromCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/remove-coupon/).

- `updateCartItems` mutation - allows you to modify items in the specified cart.
  [Learn more about updateCartItems mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/update-items/).

- `removeItemFromCart` mutation - deletes the entire quantity of a specified item from the cart.
  [Learn more about removeItemFromCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/remove-item/).

- `setShippingAddressesOnCart` mutation - sets one or more shipping addresses on a specific cart.
  [Learn more about setShippingAddressesOnCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/set-shipping-address/).

- `setBillingAddressOnCart` mutation - sets the billing address for a specific cart.
  [Learn more about setBillingAddressOnCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/set-billing-address/).

- `setShippingMethodsOnCart` mutation - sets one or more delivery methods on a cart.
  [Learn more about setShippingMethodsOnCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/set-shipping-method/).

- `setPaymentMethodOnCart` mutation - defines which payment method to apply to the cart.
  [Learn more about setPaymentMethodOnCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/set-payment-method/).

- `setGuestEmailOnCart` mutation - assigns email to the guest cart.
  [Learn more about setGuestEmailOnCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/set-guest-email/).

- `setPaymentMethodAndPlaceOrder` mutation - sets the cart payment method and converts the cart into an order. **This mutation has been deprecated**. Use the `setPaymentMethodOnCart` and `placeOrder` mutations instead.
  [Learn more about setPaymentMethodAndPlaceOrder mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/set-payment-place-order/).

- `mergeCarts` mutation - transfers the contents of a guest cart into the cart of a logged-in customer.
  [Learn more about mergeCarts mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/merge/).

- `placeOrder` mutation - converts the cart into an order and returns an order ID.
  [Learn more about placeOrder mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/place-order/).

- `addProductsToCart` mutation - adds any type of product to the shopping cart.
  [Learn more about addProductsToCart mutation](https://developer.adobe.com/commerce/webapi/graphql/schema/cart/mutations/add-products/).
  