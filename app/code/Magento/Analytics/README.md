# Magento_Analytics module

The Magento_Analytics module integrates your Magento instance with the [Magento Business Intelligence (MBI)](https://magento.com/products/business-intelligence) to use [Advanced Reporting](http://devdocs.magento.com/guides/v2.2/advanced-reporting/modules.html) functionality.

The module implements the following functionality:

* enabling subscription to the MBI and automatic re-subscription
* changing the base URL with the same MBI account remained
* declaring the configuration schemas for report data collection
* collecting the Magento instance data as reports for the MBI
* introducing API that provides the collected data
* extending Magento configuration with the module parameters:
    * subscription status (enabled/disabled)
    * industry (a business area in which the instance website works)
    * time of data collection (time of the day when the module collects data)

## Structure

Beyond the [usual module file structure](http://devdocs.magento.com/guides/v2.2/architecture/archi_perspectives/components/modules/mod_intro.html) the module contains a directory `ReportXml`.
[Report XML](http://devdocs.magento.com/guides/v2.2/advanced-reporting/report-xml.html) is a markup language used to build reports for Advanced Reporting.
The language declares SQL queries using XML declaration.

## Extensibility

We do not recommend to extend the Magento_Analytics module. It introduces an API that is purposed to transfer the collected data. Note that the API cannot be used for other needs.
