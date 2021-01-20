# Magento_CustomerDownloadableGraphQl module

This module provides type and resolver information for the GraphQl module to generate downloadable product information.

## Installation

Before installing this module, note that the Magento_CustomerDownloadableGraphQl is dependent on the following modules:

- `Magento_GraphQl`
- `Magento_DownloadableGraphQl`

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_CatalogGraphQl module. For more information about the Magento extension mechanism, see [Magento plugins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CustomerDownloadableGraphQl module.

## Additional information

You can get more information about [GraphQl In Magento 2](https://devdocs.magento.com/guides/v2.4/graphql).

### GraphQl Query

- `customerDownloadableProducts` query - retrieve the list of purchased downloadable products for the logged-in customer

[Learn more about customerDownloadableProducts query](https://devdocs.magento.com/guides/v2.4/graphql/queries/customer-downloadable-products.html).
