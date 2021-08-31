# Magento_LoginAsCustomerAssistance module

This module provides possibility to enable/disable LoginAsCustomer functionality per Customer.

## Installation

The Magento_LoginAsCustomerAssistance module creates the `login_as_customer_assistance_allowed` table in the database.

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

### Layouts

This module introduces the following layouts in the `view/adminhtml/layout` directory:
- `view/adminhtml/layout`:
    - `loginascustomer_confirmation_popup`
  
For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

This module extends ui components. The configuration files located in the directory `view/adminhtml/ui_component`:
- `customer_form`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

## Additional information

This module is a part of Login As Customer feature.

[Learn more about Login As Customer feature](https://docs.magento.com/user-guide/customers/login-as-customer.html).
