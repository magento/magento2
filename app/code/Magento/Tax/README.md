# Magento_Tax module

The Magento_Tax module provides the calculations needed to compute the consumption tax on goods and services.

The Magento_Tax module includes the following:
- Configuration of the tax rates and rules to apply.
- Configuration of tax classes that apply to:
    - Taxation on products.
    - Taxation on shipping charges.
    - Taxation on gift options (example: gift wrapping).
- Specification whether the consumption tax is "sales & use" (typically product prices are loaded without any tax) or "VAT" (typically product prices are loaded including tax).
- Specification of whether the tax total line can be toggled to display the tax details/subtotals.
- Display of prices (presented with tax, without tax, or both with and without).

The Magento_Tax module also handles special cases when computing tax, such as:
- Determining the tax on an individual item (for example, one that is being returned) when the original tax has been computed on the entire shopping cart.
    - Example country: United States.
- Being able to handle 2 or more tax rates that are applied separately (examples include a "luxury tax" on exclusive items).
- Being able to handle a subsequent tax rate that is applied after a previous one is applied (a "tax on tax" situation, which recently was a part of Canadian tax law).

## Installation details

Before installing this module, note that the Magento_Tax is dependent on the following modules:

- Magento_Catalog
- Magento_Checkout
- Magento_Config
- Magento_Customer
- Magento_Directory
- Magento_PageCache
- Magento_Quote
- Magento_Reports
- Magento_Sales
- Magento_Shipping
- Magento_Store
- Magento_User

Before disabling or uninstalling this module, note the following dependencies:

- Magento_TaxImportExport
- Magento_Weee

Refer to [how to enable or disable modules in Magento 2](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`CustomerData/` - Checkout totals js layout data provider
`Pricing/` - directory that contain tax adjustment.

For information about a typical file structure of a module, see [Module file structure](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Tax module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Tax module.

### Events

The module dispatches the following events:

- `adminhtml_cache_refresh_type` event in the `\Magento\Tax\Controller\Adminhtml\Tax\IgnoreTaxNotification::execute()` method. Parameters:
    - `type` config is a cache refresh type.
- `tax_rate_data_fetch` event in the `\Magento\Tax\Model\Calculation::getRate()` method. Parameters:
    - `request` is a Data object (`\Magento\Framework\DataObject` class).
    - `sender` is a Calculation object (`\Magento\Tax\Model\Calculation` class).
- `tax_settings_change_after` event in the `\Magento\Tax\Model\Calculation\Rule::afterSave()` method.
- `tax_settings_change_after` event in the `\Magento\Tax\Model\Calculation\Rule::afterDelete()` method.
- `tax_settings_change_after` event in the `\Magento\Tax\Model\Calculation\Rate::afterSave()` method.
- `tax_settings_change_after` event in the `\Magento\Tax\Model\Calculation\Rate::afterDelete()` method.
- `tax_settings_change_after` event in the `\Magento\Tax\Model\Calculation\Rate::deleteAllRates()` method.

For information about the event, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `sales_creditmemo_item_price`
    - `sales_invoice_item_price`
    - `sales_order_create_item_price`
    - `sales_order_item_price`
    - `tax_rate_block`
    - `tax_rate_exportcsv`
    - `tax_rate_exportxml`
    - `tax_rate_index`
    - `tax_rule_block`
    - `tax_rule_edit`
    - `tax_rule_index`
- `view/base/layout`:
    - `catalog_product_prices`
- `view/frantend/layout`:
    - `checkout_cart_index`
    - `checkout_cart_sidebar_total_renderers`
    - `checkout_index_index`
    - `checkout_item_price_renderers`
    - `sales_email_item_price`
    - `sales_order_item_price`

For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

You can extend a customer form and widgets using the configuration files located in the directories 

- `view/frontend/ui_component`:
    - `widget_recently_compared`
    - `widget_recently_viewed`

For information about a UI component, see [Overview of UI components](https://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

### Public APIs

`\Magento\Tax\Api\OrderTaxManagementInterface`:

   - Get taxes applied to an order.
  
`\Magento\Tax\Api\TaxAddressManagerInterface`:

   - Set default Tax Billing and Shipping address into customer session after address save.
   - Set default Tax Shipping and Billing addresses into customer session after login.

`\Magento\Tax\Api\TaxCalculationInterface`:

   - Calculate Tax.
   - Get default rate request.
   - Get rate request.

`\Magento\Tax\Api\TaxClassManagementInterface`:

   - Get tax class id.

`\Magento\Tax\Api\TaxClassRepositoryInterface`:

   - Get a tax class with the given tax class id.
   - Retrieve tax classes which match a specific criterion.
   - Create or update a Tax Class.
   - Delete a tax class.
   - Delete a tax class with the given tax class id.
  
`\Magento\Tax\Api\TaxRateManagementInterface`:

   - Get rates by customerTaxClassId and productTaxClassId.

`\Magento\Tax\Api\TaxRateRepositoryInterface`:

   - Create or update tax rate.
   - Get a tax rate with the given tax rate id.
   - Delete a tax rate with the given tax rate id.
   - Retrieve tax rates which match a specific criterion.
   - Delete a tax rate.

`\Magento\Tax\Api\TaxRuleRepositoryInterface`:

   - Get TaxRule.
   - Save TaxRule.
   - Delete TaxRule.
   - Delete a tax rule with the given rule id.
   - Retrieve tax rules which match a specific criterion.

[Learn detailed description of the Magento_Sales API.](https://devdocs.magento.com/guides/v2.4/mrg/ce/Sales/services.html)

For information about a public API, see [Public interfaces & APIs](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/api-concepts.html).
