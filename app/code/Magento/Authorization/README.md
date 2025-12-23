# Magento_Authorization module

This module enables management of access control list roles and rules in the application.

## Installation details

This module creates the following tables in the database using `db_schema.xml`:

- `authorization_role`
- `authorization_rule`

Before disabling or uninstalling this module, note that the `Magento_GraphQl` module depends on this module.

For information about module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.
