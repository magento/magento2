# Magento_NewRelicReporting module

This module implements integration New Relic APM and New Relic Insights with Magento, giving real-time visibility into business and performance metrics for data-driven decision making.

## Installation

Before installing this module, note that the Magento_NewRelicReporting is dependent on the following modules:

- `Magento_Store`
- `Magento_Customer`
- `Magento_Backend`
- `Magento_Catalog`
- `Magento_ConfigurableProduct`
- `Magento_Config`

This module creates the following tables in the database:

- `reporting_counts`
- `reporting_module_status`
- `reporting_orders`
- `reporting_users`
- `reporting_system_updates`

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_NewRelicReporting module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_NewRelicReporting module.

## Additional information

[Learn more about New Relic Reporting](https://experienceleague.adobe.com/docs/commerce-admin/start/reporting/new-relic-reporting.html).

### Console commands

The Magento_NewRelicReporting provides console commands:

- `bin/magento newrelic:create:deploy-marker <message> <change_log> [<user>]` - check the deploy queue for entries and create an appropriate deploy marker

[Learn more about command's parameters](https://experienceleague.adobe.com/docs/commerce-operations/reference/magento-open-source.html#newreliccreatedeploy-marker).

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `magento_newrelicreporting_cron` - runs collecting all new relic reports

[Learn how to configure and run cron in Magento.](https://experienceleague.adobe.com/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs.html).
