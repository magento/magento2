# Magento_MysqlMq module

This module provides message queue implementation based on MySQL.

This module contains a recurring script, declared in `Magento\MysqlMq\Setup\Recurring`
class. This script is executed by Magento post each schema installation or upgrade
stage and populates the queue table.

## Installation

This module creates the following tables:

- `queue` - Table storing unique queues.
- `queue_message` - Queue messages.
- `queue_message_status` - Relation table to keep associations between queues and messages.

For information about a module installation, see [Enable or disable modules](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/manage-modules).

## Additional information

For information about significant changes in patch releases, see [Release information](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/overview).

### cron options

Cron group configuration can be set in the `etc/crontab.xml` file:

- `mysqlmq_clean_messages` - clean up old messages from database.
