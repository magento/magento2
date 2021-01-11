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

For information about enabling or disabling a module, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_WishlistGraphQl module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_WishlistGraphQl module.

## Additional information

For more information about the Magento_WishlistGraphQl [Queries](#queries) and [Mutations](#mutations) see below:

### Queries {#queries}

- [`wishlist`](https://devdocs.magento.com/guides/v2.4/graphql/queries/wishlist.html)

### Mutations {#mutations}

- [`addProductsToWishlist`](https://devdocs.magento.com/guides/v2.4/graphql/mutations/add-products-to-wishlist.html)
- [`removeProductsFromWishlist`](https://devdocs.magento.com/guides/v2.4/graphql/mutations/remove-products-from-wishlist.html)
- [`updateProductsInWishlist`](https://devdocs.magento.com/guides/v2.4/graphql/mutations/update-products-in-wishlist.html)
