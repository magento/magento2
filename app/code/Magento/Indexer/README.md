## Overview
Magento_Indexer module is a base of Magento Indexing functionality.
It allows:
 - read indexers configuration,
 - represent indexers in admin,
 - regenerate indexes by cron schedule,
 - regenerate indexes from console,
 - view and reset indexer state from console,
 - view and set indexer mode from console

There are 2 modes of the Indexers: "Update on save" and "Update by schedule".
Manual full reindex can be performed via console by running `php -f bin/magento indexer:reindex` console command.