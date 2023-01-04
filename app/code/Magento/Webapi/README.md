# Magento_Webapi module

The Magento_Webapi module provides the framework for the application to expose REST and SOAP web services. It exposes an area for REST and another area for SOAP services and routes requests based on the Webapi configuration.

The Magento_Webapi module also handles deserialization of requests and serialization of responses.

## Installation details

The Magento_VaultGraphQl is dependent on the following modules:

- Magento_Authorization
- Magento_Backend
- Magento_Integration
- Magento_Store

Before disabling or uninstalling this module, note the following dependencies:

- Magento_WebapiAsync

For information about enabling or disabling a module, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

### Layouts

This module introduces the following layouts and layout handles in the directories:

- `view/adminhtml/layout`:
    - `adminhtml_integration_edit`
    - `adminhtml_integration_permissionsdialog`

For more information about a layout, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).
