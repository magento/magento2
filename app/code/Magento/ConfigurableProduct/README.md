#Magento_ConfigurableProduct module

Magento_ConfigurableProduct module introduces a new product type in the Magento application called Configurable Product.
This module is designed to extend the existing functionality of the Magento_Catalog module by adding a new product type.

Configurable Products let the customers select the variant they desire by choosing options.
For example, the store owner sells t-shirts in two colors and three sizes.

## Installation details

Before disabling or uninstalling this module, note that the following modules depend on this module:

- Magento_ConfigurableImportExport
- ConfigurableProductGraphQl
- Magento_ConfigurableProductSales
- Magento_Swatches
- Magento_WishList
- Magento_NewRelicReporting
- Magento_MsrpConfigurableProduct

For information about a module enabling or disabling in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`Pricing/` - the directory that contains solutions for the configurable product price.

## Extensibility

Extension developers can interact with the Magento_ConfigurableProduct module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_ConfigurableProduct module.

A lot of functionality in the module is on JavaScript, you can use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.

### Events

The module dispatches the following events:

- `catalog_product_validate_variations_before` event in the `\Magento\ConfigurableProduct\Model\Product\Validator\Plugin::_validateProductVariations` method. Parameters:
    - `product` - parent product object(`\Magento\Catalog\Model\Product` class).
    - `variations` - list of product variations(`array` type)

### Layouts

The module introduces layout handles in the directories:
- `view/adminhtml/layout`
- `view/base/layout`
- `view/frontend/layout`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend UI components updates using the configuration files located in the `view/adminhtml/ui_component` and `view/frontend/ui_component` directories.

For information about a UI component in Magento 2, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

`\Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface`:
- generate variation based on the same product
- get the number of product count

`\Magento\ConfigurableProduct\Api\LinkManagementInterface`:
- get all children for Configurable product
- remove configurable product option
- add a child to the configurable product by SKU

`\Magento\ConfigurableProduct\Api\OptionRepositoryInterface`:
- get an option for configurable product
- get all options for configurable product
- remove the option from configurable product
- remove the option from configurable product
- save option

## Additional information

You can get more information at the articles:
- [Configurable Product](https://docs.magento.com/user-guide/catalog/product-create-configurable.html)
- [REST API creation a configurable product tutorial](https://devdocs.magento.com/guides/v2.4/rest/tutorials/configurable-product/config-product-intro.html)
- [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html)
