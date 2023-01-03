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

The Magento_Indexer module is one of the base Magento 2 modules. You cannot disable or uninstall this module.

This module is dependent on the following modules:

- `Magento_Store`
- `Magento_AdminNotification`

The Magento_Indexer module creates the following tables in the database:
- `indexer_state`
- `mview_state`

For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Structure

`App/` - the directory that contains launch application entry point.

For information about a typical file structure of a module in Magento 2, see [Module file structure](http://devdocs.magento.com/guides/v2.4/extension-dev-guide/build/module-file-structure.html#module-file-structure).

## Extensibility

Extension developers can interact with the Magento_Indexer module. For more information about the Magento extension mechanism, see [Magento plugins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_Indexer module.

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

For information about an event in Magento 2, see [Events and observers](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/events-and-observers.html#events).

### Layouts

This module introduces the following layout handles in the `view/adminhtml/layout` directory:
- `indexer_indexer_list`
- `indexer_indexer_list_grid`

For more information about layouts in Magento 2, see the [Layout documentation](https://devdocs.magento.com/guides/v2.4/frontend-dev-guide/layouts/layout-overview.html).

## Additional information

### Indexer modes

There are 2 modes of the Indexers:

- Update on Save - index tables are updated immediately after the dictionary data is changed
- Update by Schedule - index tables are updated by cron job according to the configured schedule

### Console commands

Magento_Indexers provides console commands:
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

[Learn how to configure and run cron in Magento.](http://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html).

More information can get at articles:
- [Learn more about indexing](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/indexing.html)
- [Learn more about Indexer optimization](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/indexer-batch.html)
- [Learn more how to add custom indexer](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/indexing-custom.html)
- [Learn how to manage indexers](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-index.html)
- [Learn more about Index Management](https://docs.magento.com/user-guide/system/index-management.html)
