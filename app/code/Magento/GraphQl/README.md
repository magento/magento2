# Magento_GraphQl module

This module provides the framework for the application to expose GraphQL compliant web services. It exposes an area for
GraphQL services and resolves request data based on the generated schema. It also maps this response to a JSON object
for the client to read.

## Installation

This module is one of the base modules. You cannot disable or uninstall this module.

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

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.

## Additional information

You can get more information about GraphQl in the [Adobe Developer documentation](https://developer.adobe.com/commerce/webapi/graphql/).
