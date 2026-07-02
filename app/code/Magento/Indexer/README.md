# Magento_Indexer module

This module provides Magento Indexing functionality.
It allows to:

- read indexers configuration
- represent indexers in admin
- regenerate indexes by cron schedule
- regenerate indexes from console
- view and reset indexer state from console
- view and set indexer mode from console

## Installation

This module is one of the base modules. You cannot disable or uninstall this module.

This module is dependent on the following modules:

- `Magento_Store`
- `Magento_AdminNotification`

This module creates the following tables in the database:

- `indexer_state`
- `mview_state`

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Structure

`App/` - the directory that contains the launch application entry point.

For information about a typical file structure of a module, see [Module file structure](https://developer.adobe.com/commerce/php/development/build/component-file-structure/#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Indexer module. For more information about the extension mechanism, see [Plugins](https://developer.adobe.com/commerce/php/development/components/plugins/).

[The dependency injection mechanism](https://developer.adobe.com/commerce/php/development/components/dependency-injection/) enables you to override the functionality of the Magento_Indexer module.

### Events

The module dispatches the following events:

#### Model

- `clean_cache_by_tags` event in the `\Magento\Indexer\Model\Indexer\CacheCleaner::cleanCache` method. Parameters:
    - `object` is a `cacheContext` object (`Magento\Framework\Indexer\CacheContext` class)

#### Plugin

- `clean_cache_after_reindex` event in the `\Magento\Indexer\Model\Processor\CleanCache::afterUpdateMview` method. Parameters:
    - `object` is a `context` object (`Magento\Framework\Indexer\CacheContext` class)

- `clean_cache_by_tags` event in the `\Magento\Indexer\Model\Processor\CleanCache::afterReindexAllInvalid` method. Parameters:
    - `object` is a `context` object (`Magento\Framework\Indexer\CacheContext` class)

For information about an event, see [Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/#events).

### Layouts

This module introduces the following layout handles in the `view/adminhtml/layout` directory:

- `indexer_indexer_list`
- `indexer_indexer_list_grid`

For more information about layouts, see the [Layout documentation](https://developer.adobe.com/commerce/frontend-core/guide/layouts/).

## Additional information

### Indexer modes

There are 2 modes of the indexers:

- Update on Save - index tables are updated immediately after the dictionary data is changed
- Update by Schedule - index tables are updated by cron job according to the configured schedule

### Console commands

This module provides the following console commands:

- `bin/magento indexer:info` - view a list of all indexers
- `bin/magento indexer:status [indexer]` - view index status
- `bin/magento indexer:reindex [indexer]` - run reindex
- `bin/magento indexer:reset [indexer]` - reset indexers
- `bin/magento indexer:show-mode [indexer]` - view the current indexer configuration
- `bin/magento indexer:set-mode {realtime|schedule} [indexer]` - specify the indexer configuration
- `bin/magento indexer:set-dimensions-mode [indexer]` - set indexer dimension mode
- `bin/magento indexer:show-dimensions-mode [indexer]` - set indexer dimension mode

### Cron options

Cron group configuration can be set at `etc/crontab.xml`:

- `indexer_reindex_all_invalid` - regenerate indexes for all invalid indexers
- `indexer_update_all_views` - update indexer views
- `indexer_clean_all_changelogs` - clean indexer view changelogs

[Learn how to configure and run cron in Magento](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs).

You can get more information at the following articles:

- [Learn more about indexing](https://developer.adobe.com/commerce/php/development/components/indexing/)
- [Learn more about Indexer optimization](https://developer.adobe.com/commerce/php/development/components/indexing/optimization/)
- [Learn more how to add custom indexer](https://developer.adobe.com/commerce/php/development/components/indexing/custom-indexer/)
- [Learn how to manage indexers](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/manage-indexers)
- [Learn more about Index Management](https://experienceleague.adobe.com/en/docs/commerce-admin/systems/tools/index-management)
