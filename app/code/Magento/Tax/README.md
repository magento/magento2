# Overview
## Purpose of module
The Magento_Tax module provides the calculations needed to compute the consumption tax on goods and services.

The Magento_Tax module includes the following:
* configuration of the tax rates and rules to apply
* configuration of tax classes that apply to:
** taxation on products
** taxation on shipping charges
** taxation on gift options (example: gift wrapping)
* specification whether the consumption tax is "sales & use" (typically product prices are loaded without any tax) or "VAT" (typically product prices are loaded including tax)
* specification of whether the tax total line can be toggled to display the tax details/subtotals
* display of prices (presented with tax, without tax, or both with and without)

The Magento_Tax module also handles special cases when computing tax, such as:
* determining the tax on an individual item (for example, one that is being returned) when the original tax has been computed on the entire shopping cart
** example country: United States
* being able to handle 2 or more tax rates that are applied separately (examples include a "luxury tax" on exclusive items)
* being able to handle a subsequent tax rate that is applied after a previous one is applied (a "tax on tax" situation, which recently was a part of Canadian tax law)

# Deployment
## System requirements
The Magento_Tax module does not have any specific system requirements.

Depending on how many tax rates and tax rules are being used, there might be consideration for the database size
Depending on the frequency of updating tax rates and tax rules, there might be consideration for the scheduling of these updates

## Install
The Magento_Tax module is installed automatically (using the native Magento install mechanism) without any additional actions.

## Uninstall
The Magento_Tax module should not be uninstalled; it is a required module.


###Layouts

The module interacts with the following layout handles:

`view/base/layout` directory:
 - `catalog_product_prices.xml`
The module interacts with the following layout handles:

`view/adminhtml/layout` directory:
 - `sales_creditmemo_item_price.xml`
 - `sales_invoice_item_price.xml`
 - `sales_order_create_item_price.xml`
 - `sales_order_item_price.xml`
 - `tax_rate_block.xml`
 - `tax_rate_exportcsv.xml`
 - `tax_rate_exportxml.xml`
 - `tax_rate_index.xml`
 - `tax_rule_block.xml`
 - `tax_rule_edit.xml`
 - `tax_rule_index.xml`
The module interacts with the following layout handles in the `view/frontend/layout` directory:
 - `checkout_cart_index.xml`
 - `checkout_cart_sidebar_total_renderers.xml`
 - `checkout_index_index.xml`
 - `checkout_item_price_renderers.xml`
 - `sales_email_item_price.xml`
 - `sales_order_item_price.xml`
 
## Extensibility

Extension developers can interact with the Magento_CatalogUrlRewrite module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_CatalogUrlRewrite module.

A lot of functionality in the module is on JavaScript, use [mixins](https://devdocs.magento.com/guides/v2.4/javascript-dev-guide/javascript/js_mixins.html) to extend it.