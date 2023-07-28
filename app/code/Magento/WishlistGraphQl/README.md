# Magento_WishlistGraphQl module

The Magento_WishlistGraphQl module adds, removes, and updates products on the wishlist.

The Magento_WishlistGraphQl module extends Magento_GraphQl and Magento_Wishlist modules. This module provides type and resolver information for GraphQL API.

## Installation details

Before installing this module, note that the Magento_WishlistGraphQl is dependent on the following modules:

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

For information about enabling or disabling a module, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_WishlistGraphQl module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_WishlistGraphQl module.

## Additional information

For more information about the Magento_WishlistGraphQl queries and mutations see below:

### Queries

- [`wishlist`](https://developer.adobe.com/commerce/webapi/graphql/usage/wishlist.html)

### Mutations

- [`addProductsToWishlist`](https://developer.adobe.com/commerce/webapi/graphql/schema/wishlist/mutations/add-products/)
- [`removeProductsFromWishlist`](https://developer.adobe.com/commerce/webapi/graphql/mutations/remove-products-from-wishlist.html)
- [`updateProductsInWishlist`](https://developer.adobe.com/commerce/webapi/graphql/mutations/update-products-in-wishlist.html)
