# Magento_LoginAsCustomerLog module

This module provides log for Login as Customer functionality

## Installation

The Magento_LoginAsCustomerLog module creates the `magento_login_as_customer_log` table in the database.

For information about a module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

### Layouts

This module introduces the following layouts in the `view/adminhtml/layout` directory:

- `loginascustomer_log_log_index`

For more information about a layout in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

### UI components

You can extend log listing updates using the configuration files located in the directories

- `view/adminhtml/ui_component`:
    - `login_as_customer_log_listing`

For information about a UI component in Magento 2, see [Overview of UI components](https://developer.adobe.com/commerce/frontend-core/ui-components/).

### Public APIs

- `\Magento\LoginAsCustomerLog\Api\Data\LogInterface`
    - login as customer log data

- `\Magento\LoginAsCustomerLog\Api\Data\LogSearchResultsInterface`
    - login as customer log entity search results data

- `\Magento\LoginAsCustomerLog\Api\GetLogsListInterface`:
    - get login as customer log list considering search criteria

- `\Magento\LoginAsCustomerLog\Api\SaveLogsInterface`:
    - save login as custom logs entities

For information about a public API in Magento 2, see [Public interfaces & APIs](https://developer.adobe.com/commerce/php/development/components/api-concepts/).

## Additional information

This module is a part of Login As Customer feature.

[Learn more about Login As Customer feature](https://experienceleague.adobe.com/docs/commerce-admin/customers/customer-accounts/manage/login-as-customer.html).
