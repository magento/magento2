# Magento_MysqlMq module

**Magento_MysqlMq** provides message queue implementation based on MySQL.

Module contain recurring script, declared in `Magento\MysqlMq\Setup\Recurring` 
class. This script is executed by Magento post each schema installation or upgrade
stage and populates the queue table.

## Installation

Module creates the following tables:

- `queue` - Table storing unique queues
- `queue_message` - Queue messages
- `queue_message_status` - Relation table to keep associations between queues and messages


For information about a module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Additional information

For information about significant changes in patch releases, see [2.3.x Release information](http://devdocs.magento.com/guides/v2.3/release-notes/bk-release-notes.html).

### cron options

cron group configuration can be set in `etc/crontab.xml`.

- `mysqlmq_clean_messages` - clean up old messages from database
