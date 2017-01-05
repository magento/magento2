<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'queue'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('queue')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Queue ID'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Queue name'
        )->addIndex(
            $installer->getIdxName(
                'queue',
                ['name'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['name'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Table storing unique queues'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'queue_message'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('queue_message')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Message ID'
        )->addColumn(
            'topic_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Message topic'
        )->addColumn(
            'body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
            [],
            'Message body'
        )->setComment(
            'Queue messages'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'queue_message_status'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('queue_message_status')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Relation ID'
        )->addColumn(
            'queue_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Queue ID'
        )->addColumn(
            'message_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Message ID'
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Message status in particular queue'
        )->addColumn(
            'number_of_trials',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Number of trials to processed failed message processing'
        )->addIndex(
            $installer->getIdxName(
                'queue_message_status',
                ['queue_id', 'message_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['queue_id', 'message_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $installer->getIdxName(
                'queue_message_status',
                ['status', 'updated_at'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['status', 'updated_at'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $installer->getFkName('queue_message', 'id', 'queue_message_status', 'message_id'),
            'message_id',
            $installer->getTable('queue_message'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('queue', 'id', 'queue_message_status', 'queue_id'),
            'queue_id',
            $installer->getTable('queue'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Relation table to keep associations between queues and messages'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
