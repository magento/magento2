# Magento_CustomerGraphQl module

This module provides type and resolver information for the GraphQl module to generate customer information endpoints.

## Installation

Before installing this module, note that the Magento_CustomerGraphQl is dependent on the following modules:

- `Magento_GraphQl`
- `Magento_Customer`

Before disabling or uninstalling this module, note that the following modules depends on this module:

- `Magento_WishlistGraphQl`

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_CustomerGraphQl module. For more information about the Magento extension mechanism, see [Magento plugins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CustomerGraphQl module.

## Additional information

You can get more information about [GraphQl In Magento 2](https://devdocs.magento.com/guides/v2.4/graphql).

### GraphQl Query

- `customer` query - returns information about the logged-in customer, store credit history and customer’s wishlist
- `isEmailAvailable` query - checks whether the specified email has already been used to create a customer account. A value of true indicates the email address is available, and the customer can use the email address to create an account

[Learn more about customer query](https://devdocs.magento.com/guides/v2.4/graphql/queries/customer.html).
[Learn more about isEmailAvailable query](https://devdocs.magento.com/guides/v2.4/graphql/queries/is-email-available.html).
