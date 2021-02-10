# Magento_Analytics module

The Magento_Analytics module integrates your Magento instance with the [Magento Business Intelligence (MBI)](https://magento.com/products/business-intelligence) to use [Advanced Reporting](https://devdocs.magento.com/guides/v2.4/advanced-reporting/modules.html) functionality.

The module implements the following functionality:

- Enabling subscription to Magento Business Intelligence (MBI) and automatic re-subscription
- Declaring the configuration schemas for report data collection
- Collecting the Magento instance data as reports for MBI
- Introducing API that provides the collected data
- Extending Magento configuration with the module parameters:
  - Subscription status (enabled/disabled)
  - Industry (a business area in which the instance website works)
  - Time of data collection (time of the day when the module collects data)

## Installation details

Before disabling or uninstalling this module, note that the following modules depends on this module:

- Magento_CatalogAnalytics
- Magento_CustomerAnalytics
- Magento_QuoteAnalytics
- Magento_ReviewAnalytics
- Magento_SalesAnalytics
- Magento_WishlistAnalytics

## Structure

Beyond the [usual module file structure](https://devdocs.magento.com/guides/v2.4/architecture/archi_perspectives/components/modules/mod_intro.html) the module contains a directory `ReportXml`.
[Report XML](https://devdocs.magento.com/guides/v2.4/advanced-reporting/report-xml.html) is a markup language used to build reports for Advanced Reporting.
The language declares SQL queries using XML declaration.

## Subscription Process

The subscription to the MBI service is enabled during the installation process of the Analytics module. Each administrator will be notified of these new features upon their initial login to the Admin Panel.

## Analytics Settings

Configuration settings for the Analytics module can be modified in the Admin Panel on the Stores > Configuration page under the General > Advanced Reporting tab.

The following options can be adjusted:

- Advanced Reporting Service (Enabled/Disabled)
  - Alters the status of the Advanced Reporting subscription
- Time of day to send data (Hour/Minute/Second in the store's time zone)
  - Defines when the data collection process for the Advanced Reporting service occurs
- Industry
  - Defines the industry of the store in order to create a personalized Advanced Reporting experience

## Extensibility

We do not recommend to extend the Magento_Analytics module. It introduces an API that is purposed to transfer the collected data. Note that the API cannot be used for other needs.
