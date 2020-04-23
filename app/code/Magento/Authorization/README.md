# Magento_Authorization module

The Magento_Authorization module enables management of access control list roles and rules in the application.

## Installation details

The Magento_AdminNotification module creates the following tables in the database:

- `authorization_role`
- `authorization_rule`

Before disabling or uninstalling this module, note that the Magento_GraphQl module depends on this module.

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.3/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_Authorization module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Authorization module.
