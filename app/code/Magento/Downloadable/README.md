#Magento_Downloadable module.

Magento_Downloadable module introduces a new product type in the Magento application called Downloadable Product.

This module is designed to extend the existing functionality of the Magento_Catalog module by adding a new product type.

## Installation details

Before disabling or uninstalling this module, note that the following modules depend on this module:

- `Magento_DownloadableImportExport`
- `Magento_DownloadableGraphQl`
- `Magento_Msrp`
- `Magento_Reports`
- `Magento_RemoteStorage`
- `Magento_Wishlist`

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Pricing/` - the directory that contains solutions for the downloadable product price.

## Extensibility

Extension developers can interact with the Magento_ConfigurableProduct module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_ConfigurableProduct module.

Also, you can use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend JavaScript functionality.

### Layouts

The module introduces layout handles in the directories:
- `view/adminhtml/layout`
- `view/frontend/layout`

### Public APIs

`\Magento\Downloadable\Api\DomainManagerInterface`:
- get the whitelist.
- add the host to the whitelist.
- remove the host from the whitelist.

`\Magento\Downloadable\Api\LinkRepositoryInterface`:
- get a list of links with associated samples by SKU
- get a list of links with associated samples by product
- update/create a downloadable link to the given product.
- delete downloadable link.

`\Magento\Downloadable\Api\SampleRepositoryInterface`:
- get a list of samples for the downloadable product.
- get a list of links with associated samples.
- update/save a downloadable sample of the given product.
- delete downloadable sample.

## Additional information

You can get more information at the articles:
- [Downloadable Product](https://docs.magento.com/user-guide/catalog/product-create-downloadable.html)
- [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html)
