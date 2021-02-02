# Magento_Msrp module

The **Magento_Msrp** module is responsible for Manufacturer’s Suggested Retail Price functionality.
A current module provides base functional for msrp pricing rendering, configuration and calculation.

## Installation
The Magento_Msrp module creates the following attributes:

Entity type - `catalog_product`.

Attribute group - `Advanced Pricing`.

- `msrp` - Manufacturer's Suggested Retail Price
- `msrp_display_actual_price_type` -Display Actual Price

Before disabling or uninstalling this module, note that the following modules depends on this module:

- `Magento_MsrpConfigurableProduct`
- `Magento_MsrpGroupedProduct`
- `Magento_ConfigurableProduct`

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure
`Pricing\` - directory contains interfaces and implementation for msrp pricing calculations
 (`\Magento\Msrp\Pricing\MsrpPriceCalculatorInterface`), price renderers 
 and price models.
 
`Pricing\Price\` - the directory contains declares msrp price model interfaces and implementations.

`Pricing\Renderer\` - contains price renderers implementations.

For information about a typical file structure of a module in Magento 2,
 see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).
 
## Extensibility
 
 Developers can pass custom `msrpPriceCalculators` for `Magento\Msrp\Pricing\MsrpPriceCalculator` using type configuration using  `di.xml`. 
 
 For example:
 ```
    <type name="Magento\Msrp\Pricing\MsrpPriceCalculator">
        <arguments>
            <argument name="msrpPriceCalculators" xsi:type="array">
                <item name="configurable" xsi:type="array">
                    <item name="productType" xsi:type="const">Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE</item>
                    <item name="priceCalculator" xsi:type="object">Magento\MsrpConfigurableProduct\Pricing\MsrpPriceCalculator</item>
                </item>
            </argument>
       </arguments>
   </type>
``` 
 
 Extension developers can interact with the Magento_Msrp module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Msrp module.

### Events

This module observes the following event:

`etc/frontend/`

 - `sales_quote_collect_totals_after` in the `Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver` file. 

`etc/webapi_rest`
 - `sales_quote_collect_totals_after` in the `Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver` file. 

`etc/webapi_soap`
 - `sales_quote_collect_totals_after` in the `Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver` file. 

For information about an event in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts:

`view/base/layout` directory:

- `catalog_product_prices`
- `cms_index_index`

`view/frontend/layout` directory:

- `catalog_category_view`
- `catalog_product_compare_index`
- `catalog_product_view`
- `catalogsearch_advanced_result`
- `catalogsearch_result_index`
- `checkout_cart_sidebar_total_renderers`
- `checkout_onepage_failure`
- `checkout_onepage_success`
- `msrp_popup`
- `review_product_list`
- `wishlist_index_configure_type_downloadable`
- `wishlist_index_index`
- `wishlist_search_view`
- `wishlist_shared_index`

### UI components

Module provides product admin form modifier: 

`Magento\Msrp\Ui\DataProvider\Product\Form\Modifier\Msrp` - removes `msrp_display_actual_price_type` field from the form if config disabled else adds `validate-zero-or-greater` validation to the fild.

## Additional information

### Catalog attributes

A current module extends `etc/catalog_attributes.xml` and provides following attributes for `quote_item` group:
- `msrp`
- `msrp_display_actual_price_type`

### Extension Attributes
The Magento_Msrp provides extension attributes for `Magento\Catalog\Api\Data\ProductRender\PriceInfoInterface`
- attribute code: `msrp`
- attribute type: `Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterface`
