# Magento_WishlistGraphQl module

This module adds, removes, and updates products on the wishlist.

This module extends Magento_GraphQl and Magento_Wishlist modules. This module provides type and resolver information for GraphQL API.

## Installation details

Before installing this module, note that this module is dependent on the following modules:

- Magento_Catalog
- Magento_Checkout
- Magento_Customer
- Magento_CustomerGraphQl
- Magento_Directory
- Magento_GiftMessage
- Magento_GraphQl
- Magento_Quote
- Magento_Sales
- Magento_Store

For information about enabling or disabling a module, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.

## Additional information

For more information about the queries and mutations, see below:

### Queries

- [`wishlist`](https://developer.adobe.com/commerce/webapi/graphql/schema/wishlist/queries/wishlist/)

### Mutations

- [`addProductsToWishlist`](https://developer.adobe.com/commerce/webapi/graphql/schema/wishlist/mutations/add-products/)
- [`removeProductsFromWishlist`](https://developer.adobe.com/commerce/webapi/graphql/schema/wishlist/mutations/remove-products/)
- [`updateProductsInWishlist`](https://developer.adobe.com/commerce/webapi/graphql/schema/wishlist/mutations/update-products/)
