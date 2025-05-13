# Magento_Backup module

The Magento_Backup module allows administrators to perform backups and rollbacks. Types of backups include system, database and media backups. This module relies on the Cron module to schedule backups.

The Magento_Backup module does not affect the storefront.

For more information about this module, see [Backups](https://experienceleague.adobe.com/docs/commerce-admin/systems/tools/backups.html)

## Extensibility

Extension developers can interact with the Magento_Backup module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The Magento dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Backup module.

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

`backup_index_block`
`backup_index_disabled`
`backup_index_grid`
`backup_index_index`

For more information about layouts in Magento 2, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

For information about significant changes in patch releases, see [Release information](https://experienceleague.adobe.com/docs/commerce-operations/release/notes/overview.html).
