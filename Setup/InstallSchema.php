<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

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
         * Create table 'magento_bulk'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magento_bulk')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Bulk Internal ID (must not be exposed)'
        )->addColumn(
            'uuid',
            Table::TYPE_VARBINARY,
            39,
            [],
            'Bulk UUID (can be exposed to reference bulk entity)'
        )->addColumn(
            'user_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'ID of the user that performed an action'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            255,
            [],
            'Bulk Description'
        )->addColumn(
            'operation_count',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Total number of operations scheduled within this bulk'
        )->addColumn(
            'start_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Bulk start time'
        )->addIndex(
            $installer->getIdxName('magento_bulk', ['uuid'], AdapterInterface::INDEX_TYPE_UNIQUE),
            ['uuid'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('magento_bulk', 'user_id', 'admin_user', 'user_id'),
            'user_id',
            $installer->getTable('admin_user'),
            'user_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Bulk entity that represents set of related asynchronous operations'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'magento_operation'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magento_operation')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Operation ID'
        )->addColumn(
            'bulk_uuid',
            Table::TYPE_VARBINARY,
            39,
            [],
            'Related Bulk UUID'
        )->addColumn(
            'topic_name',
            Table::TYPE_TEXT,
            255,
            [],
            'Name of the related message queue topic'
        )->addColumn(
            'serialized_data',
            Table::TYPE_BLOB,
            null,
            [],
            'Data (serialized) required to perform an operation'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['default' => 0],
            'Operation status (OPEN | COMPLETE | RETRIABLY_FAILED | NOT_RETRIABLY_FAILED)'
        )->addColumn(
            'error_code',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Code of the error that appeared during operation execution (used to aggregate related failed operations)'
        )->addColumn(
            'result_message',
            Table::TYPE_TEXT,
            255,
            [],
            'Operation result message'
        )->addIndex(
            $installer->getIdxName('magento_operation', ['bulk_uuid', 'error_code']),
            ['bulk_uuid', 'error_code']
        )->addForeignKey(
            $installer->getFkName('magento_operation', 'bulk_uuid', 'magento_bulk', 'uuid'),
            'bulk_uuid',
            $installer->getTable('magento_bulk'),
            'uuid',
            Table::ACTION_CASCADE
        )->setComment(
            'Operation entity'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'magento_acknowledged_bulk'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('magento_acknowledged_bulk')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Internal ID'
        )->addColumn(
            'bulk_uuid',
            Table::TYPE_VARBINARY,
            39,
            [],
            'Related Bulk UUID'
        )->addIndex(
            $installer->getIdxName('magento_acknowledged_bulk', ['bulk_uuid'], AdapterInterface::INDEX_TYPE_UNIQUE),
            ['bulk_uuid'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('magento_acknowledged_bulk', 'bulk_uuid', 'magento_bulk', 'uuid'),
            'bulk_uuid',
            $installer->getTable('magento_bulk'),
            'uuid',
            Table::ACTION_CASCADE
        )->setComment(
            'Bulk that was viewed by user from notification area'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
