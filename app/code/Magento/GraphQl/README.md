# Magento_GraphQl module

This module provides the framework for the application to expose GraphQL compliant web services. It exposes an area for
GraphQL services and resolves request data based on the generated schema. It also maps this response to a JSON object
for the client to read.

## Installation

The Magento_GraphQl module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

This module is dependent on the following modules:

- `Magento_Authorization`
- `Magento_Eav`

The following modules depend on this module:

- `Magento_BundleGraphQl`
- `Magento_CatalogGraphQl`
- `Magento_CmsGraphQl`
- `Magento_CompareListGraphQl`
- `Magento_ConfigurableProductGraphQl`
- `Magento_DownloadableGraphQl`
- `Magento_EavGraphQl`
- `Magento_GraphQlCache`
- `Magento_GroupedProductGraphQl`
- `Magento_ReviewGraphQl`
- `Magento_StoreGraphQl`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_GraphQl module. For more information about the Magento extension mechanism, see [Magento plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_GraphQl module.

## Additional information

You can get more information about [GraphQl In Magento 2](https://developer.adobe.com/commerce/webapi/graphql/).
