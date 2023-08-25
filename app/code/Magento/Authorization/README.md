# Magento_Authorization module

The Magento_Authorization module enables management of access control list roles and rules in the application.

## Installation details

The Magento_Authorization module creates the following tables in the database using `db_schema.xml`:

- `authorization_role`
- `authorization_rule`

Before disabling or uninstalling this module, note that the Magento_GraphQl module depends on this module.

For information about module installation in Magento 2, see [Enable or disable modules](https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/tutorials/manage-modules.html).

## Extensibility

Extension developers can interact with the Magento_Authorization module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Authorization module.
