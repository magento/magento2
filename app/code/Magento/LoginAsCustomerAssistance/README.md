# Magento_LoginAsCustomerAssistance module

This module provides possibility to enable/disable LoginAsCustomer functionality per Customer.

## Installation

The Magento_LoginAsCustomerAssistance module creates the `login_as_customer_assistance_allowed` table in the database.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_LoginAsCustomerAssistance module. For more information about the Magento extension mechanism, see [Magento plugins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_LoginAsCustomerAssistance module.

### Layouts

This module introduces the following layouts in the `view/adminhtml/layout` and  `view/frontend/layout` directories:
- `view/adminhtml/layout`:
    - `loginascustomer_confirmation_popup`
- `view/frontend/layout`:
    - `customer_account_create`
    - `customer_account_edit`
    
For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

This module extends ui components. The configuration files located in the directory `view/adminhtml/ui_component`:
- `customer_form`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

## Additional information

This module is a part of Login As Customer feature.

[Learn more about Login As Customer feature](https://docs.magento.com/user-guide/customers/login-as-customer.html).
