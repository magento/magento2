# Magento_Analytics Module

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

## Subscription Process

The subscription to the MBI service is enabled during the installation process of the Analytics module. Each administrator will be notified of these new features upon their initial login to the Admin Panel.

## Analytics Settings

Configuration settings for the Analytics module can be modified in the Admin Panel on the Stores > Configuration page under the General > Advanced Reporting tab.

The following options can be adjusted:
* Advanced Reporting Service (Enabled/Disabled)
    * Alters the status of the Advanced Reporting subscription
* Time of day to send data (Hour/Minute/Second in the store's time zone)
    * Defines when the data collection process for the Advanced Reporting service occurs
* Industry
    * Defines the industry of the store in order to create a personalized Advanced Reporting experience

## Extensibility

We do not recommend to extend the Magento_Analytics module. It introduces an API that is purposed to transfer the collected data. Note that the API cannot be used for other needs.
