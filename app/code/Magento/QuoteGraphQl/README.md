# Magento_QuoteGraphQl module

This module provides type and resolver information for the GraphQl module
to generate quote (cart) information endpoints. Also provides endpoints for modifying a quote.

## Installation

Before installing this module, note that the Magento_QuoteGraphQl is dependent on the following modules:
- `Magento_CatalogGraphQl`

This module does not introduce any database schema modifications or new data.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_QuoteDownloadableLinks module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_QuoteDownloadableLinks module.

## Additional information

You can get more information about [GraphQl In Magento 2](https://devdocs.magento.com/guides/v2.4/graphql).

### GraphQl Query

- `cart` query - retrieve information about a particular cart.
[Learn more about cart query](https://devdocs.magento.com/guides/v2.4/graphql/queries/cart.html).
  
- `customerCart` query - returns the active cart for the logged-in customer. If the cart does not exist, the query creates one.
[Learn more about customerCart query](https://devdocs.magento.com/guides/v2.4/graphql/queries/customer-cart.html).

### GraphQl Mutation

- `createEmptyCart` mutation - creates an empty shopping cart for a guest or logged in customer.
[Learn more about createEmptyCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/create-empty-cart.html).

- `addSimpleProductsToCart` mutation - allows you to add any number of simple and group products to the cart at the same time.
  [Learn more about addSimpleProductsToCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/add-simple-products.html).

- `addVirtualProductsToCart` mutation - allows you to add multiple virtual products to the cart at the same time, but you cannot add other product types with this mutation.
  [Learn more about addVirtualProductsToCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/add-virtual-products.html).

- `applyCouponToCart` mutation - applies a pre-defined coupon code to the specified cart.
  [Learn more about applyCouponToCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/apply-coupon.html).

- `removeCouponFromCart` mutation - removes a previously-applied coupon from the cart.
  [Learn more about removeCouponFromCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/remove-coupon.html).

- `updateCartItems` mutation - allows you to modify items in the specified cart.
  [Learn more about updateCartItems mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/update-cart-items.html).

- `removeItemFromCart` mutation - deletes the entire quantity of a specified item from the cart.
  [Learn more about removeItemFromCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/remove-item.html).

- `setShippingAddressesOnCart` mutation - sets one or more shipping addresses on a specific cart.
  [Learn more about setShippingAddressesOnCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/set-shipping-address.html).

- `setBillingAddressOnCart` mutation - sets the billing address for a specific cart.
  [Learn more about setBillingAddressOnCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/set-billing-address.html).

- `setShippingMethodsOnCart` mutation - sets one or more delivery methods on a cart.
  [Learn more about setShippingMethodsOnCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/set-shipping-method.html).

- `setPaymentMethodOnCart` mutation - defines which payment method to apply to the cart.
  [Learn more about setPaymentMethodOnCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/set-payment-method.html).

- `setGuestEmailOnCart` mutation - assigns email to the guest cart.
  [Learn more about setGuestEmailOnCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/set-guest-email.html).

- `setPaymentMethodAndPlaceOrder` mutation - sets the cart payment method and converts the cart into an order. **This mutation has been deprecated**. Use the `setPaymentMethodOnCart` and `placeOrder` mutations instead.
  [Learn more about setPaymentMethodAndPlaceOrder mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/set-payment-place-order.html).

- `mergeCarts` mutation - transfers the contents of a guest cart into the cart of a logged-in customer.
  [Learn more about mergeCarts mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/merge-carts.html).

- `placeOrder` mutation - converts the cart into an order and returns an order ID.
  [Learn more about placeOrder mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/place-order.html).

- `addProductsToCart` mutation - adds any type of product to the shopping cart.
  [Learn more about addProductsToCart mutation](https://devdocs.magento.com/guides/v2.4/graphql/mutations/add-products-to-cart.html).
  