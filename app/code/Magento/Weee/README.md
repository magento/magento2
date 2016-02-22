# Overview
The Magento_Weee module enables the application of fees/fixed product taxes (FPT) on certain types of products, usually related to electronic devices and recycling.
Fixed product taxes can be used to setup a WEEE tax that is a fixed amount, rather than a percentage of the product price. FPT can be configured to be displayed at various places in Magento. Rules, amounts, and display options can be configured in the backend. This module extends the existing functionality of Magento_Tax.

The Magento_Wee module includes the following:

* ability to add different number of fixed product taxes to product. They are treated as a product attribute;
* configuration of where Weee appears (on category, product, sales, invoice, or credit memo pages) and whether FPT should be taxed;
* a new line item in the totals section.

# System requirements
The Magento_Weee module does not have any specific system requirements.

## Install
Magento_Weee module can be installed automatically (using native Magento install mechanism) without any additional actions

## Uninstall
Magento installation with existing products with FPT:
* Disable FPT on the backend
* Remove all products with FPT
* Remove all FPT attributes from attribute sets
* Delete all FPT attributes
* Remove module directory from the code base
* New Magento installation:
* Can be removed without additional actions