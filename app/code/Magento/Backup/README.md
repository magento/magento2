# Magento_Backup module

This module allows administrators to perform backups and rollbacks. Types of backups include system, database and media backups. This module relies on the Cron module to schedule backups.

This module does not affect the storefront.

For more information about this module, see [Backups](https://experienceleague.adobe.com/en/docs/commerce-admin/systems/tools/backups).

## Extensibility

Extension developers can interact with this module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of this module.

### Layouts

This module introduces the following layouts and layout handles in the `view/adminhtml/layout` directory:

`backup_index_block`
`backup_index_disabled`
`backup_index_grid`
`backup_index_index`

For more information about layouts, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

For information about significant changes in patch releases, see [Release information](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/overview).
