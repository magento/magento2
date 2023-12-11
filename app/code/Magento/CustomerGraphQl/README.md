# Magento_CustomerGraphQl module

This module provides type and resolver information for the GraphQl module to generate customer information endpoints.

## Installation

Before installing this module, note that the Magento_CustomerGraphQl is dependent on the following modules:

- `Magento_GraphQl`
- `Magento_Customer`

Before disabling or uninstalling this module, note that the following modules depends on this module:

- `Magento_WishlistGraphQl`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_CustomerGraphQl module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_CustomerGraphQl module.

## Additional information

You can get more information about [GraphQl In Magento 2](https://developer.adobe.com/commerce/webapi/graphql/).

### GraphQl Query

- `customer` query - returns information about the logged-in customer, store credit history and customer’s wishlist
- `isEmailAvailable` query - checks whether the specified email has already been used to create a customer account. A value of true indicates the email address is available, and the customer can use the email address to create an account

[Learn more about customer query](https://developer.adobe.com/commerce/webapi/graphql/schema/customer/queries/customer/).
[Learn more about isEmailAvailable query](https://developer.adobe.com/commerce/webapi/graphql/usage/is-email-available.html).
