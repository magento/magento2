# Magento_Msrp module

The **Magento_Msrp** module is responsible for Manufacturer's Suggested Retail Price functionality.
A current module provides base functional for msrp pricing rendering, configuration and calculation.

## Installation

The Magento_Msrp module creates the following attributes:

Entity type - `catalog_product`.

Attribute group - `Advanced Pricing`.

- `msrp` - Manufacturer's Suggested Retail Price
- `msrp_display_actual_price_type` -Display Actual Price

**Pay attention** if described attributes not removed when the module is removed/disabled, it would trigger errors
because they use models and blocks from Magento_Msrp module:

- `\Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type`
- `\Magento\Msrp\Model\Product\Attribute\Source\Type\Price`
- `\Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type\Price`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Structure

`Pricing\` - directory contains interfaces and implementation for msrp pricing calculations
 (`\Magento\Msrp\Pricing\MsrpPriceCalculatorInterface`), price renderers
 and price models.

`Pricing\Price\` - the directory contains declares msrp price model interfaces and implementations.

`Pricing\Renderer\` - contains price renderers implementations.

For information about a typical file structure of a module in Magento 2,
 see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

 Developers can pass custom `msrpPriceCalculators` for `Magento\Msrp\Pricing\MsrpPriceCalculator` using type configuration using  `di.xml`.

 For example:

 ```xml
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

 More information about [type configuration](https://developer.adobe.com/commerce/php/development/build/dependency-injection-file/).

 Extension developers can interact with the Magento_Msrp module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Msrp module.

### Events

This module observes the following event:

`etc/frontend/`

 - `sales_quote_collect_totals_after` in the `Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver` file.

`etc/webapi_rest`

 - `sales_quote_collect_totals_after` in the `Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver` file.

`etc/webapi_soap`

 - `sales_quote_collect_totals_after` in the `Magento\Msrp\Observer\Frontend\Quote\SetCanApplyMsrpObserver` file.

For information about an event in Magento 2, see [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/#events).

### Layouts

The module interacts with the following layout handles:

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
- `review_product_list`
- `wishlist_index_configure_type_downloadable`
- `wishlist_index_index`
- `wishlist_search_view`
- `wishlist_shared_index`

This module introduces the following layouts and layout handles:

`view/frontend/layout` directory:

- `msrp_popup`

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
