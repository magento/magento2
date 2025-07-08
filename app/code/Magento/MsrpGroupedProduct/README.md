# Magento_MsrpGroupedProduct module

**Magento_MsrpGroupedProduct** module provides type and resolver information for the Msrp module from the GroupedProduct module.
Provides implementation of msrp price calculation for Grouped Product.

## Installation

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html)

## Structure

`Pricing\` - directory contains implementation of msrp price calculation
for Configurable Product (`Magento\MsrpConfigurableProduct\Pricing\MsrpPriceCalculator` class).

For information about a typical file structure of a module in Magento 2,
 see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

 Extension developers can interact with the Magento_Msrp module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Msrp module.

### Layouts

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

## Additional information

### collection attributes

Module adds attribute `msrp` to select for the `Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection`
in `Magento\MsrpGroupedProduct\Plugin\Model\Product\Type\Grouped` plugin.

For information about significant changes in patch releases, see [2.4.x Release information](https://experienceleague.adobe.com/docs/commerce-operations/release/notes/overview.html).
