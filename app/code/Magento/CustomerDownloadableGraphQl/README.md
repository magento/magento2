# Magento_CustomerDownloadableGraphQl module

This module provides type and resolver information for the GraphQl module to generate downloadable product information.

## Installation

Before installing this module, note that the Magento_CustomerDownloadableGraphQl is dependent on the following modules:

- `Magento_GraphQl`
- `Magento_DownloadableGraphQl`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_CatalogGraphQl module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_CustomerDownloadableGraphQl module.

## Additional information

You can get more information about [GraphQl In Magento 2](https://developer.adobe.com/commerce/webapi/graphql/).

### GraphQl Query

- `customerDownloadableProducts` query - retrieve the list of purchased downloadable products for the logged-in customer

[Learn more about customerDownloadableProducts query](https://developer.adobe.com/commerce/webapi/graphql/schema/customer/queries/downloadable-products/).
