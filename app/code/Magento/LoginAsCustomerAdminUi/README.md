# Magento_LoginAsCustomerAdminUi module

This module provides UI for Admin Panel for Login As Customer functionality.

## Installation

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_LoginAsCustomerAdminUi module. For more information about the Magento extension mechanism, see [Magento plugins](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_LoginAsCustomerAdminUi module.

### Layouts

This module introduces the following layouts in the `view/adminhtml/layout` directory:
- `adminhtml_order_shipment_view`
- `customer_index_edit`
- `loginascustomer_confirmation_popup`
- `sales_order_creditmemo_view`
- `sales_order_invoice_view`
- `sales_order_view`

For more information about a layout in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

### UI components

This module extends ui components. The configuration files located in the directory `view/adminhtml/ui_component`:
- `customer_form`

For information about a UI component in Magento 2, see [Overview of UI components](http://devdocs.magento.com/guides/v2.4/ui_comp_guide/bk-ui_comps.html).

## Additional information

This module is a part of Login As Customer feature.

[Learn more about Login As Customer feature](https://docs.magento.com/user-guide/customers/login-as-customer.html).
