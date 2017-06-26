# Magento_Analytics module

The Magento_Analytics module integrates your instance with the [Magento Business Intelligence](https://magento.com/products/business-intelligence) to use [Advanced Reporting](http://devdocs.magento.com/guides/v2.2/advanced-reporting/modules.html) functionality.

The module implements the following functionality:

* Provides a subscription and restores subscription procedures
* Declares the configuration of data collected for reporting
* Processes the data collection
* Provides an Access Control List (ACL)
* Implements a Configuration page

## Structure

The module contains a directory `ReportXml`.
[Report XML](http://devdocs.magento.com/guides/v2.2/advanced-reporting/report-xml.html) is a markup language used to build reports for Advanced Reporting.
The language declares SQL queries using XML declaration.

## Extensibility

The module doesn't expect to be extended.
It uses a specific API for transferring the collected data to the Magento Business Intelligence service but the API cannot be used for needs other than data transfer to MBI.
