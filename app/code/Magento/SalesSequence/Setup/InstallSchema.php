<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var string
     */
    private static $connectionName = 'sales';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create table 'sales_sequence_profile'
         */
        $table = $installer->getConnection(self::$connectionName)->newTable(
            $installer->getTable('sales_sequence_profile', self::$connectionName)
        )->addColumn(
            'profile_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'meta_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addColumn(
            'prefix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => true, 'primary' => false],
            'Prefix'
        )->addColumn(
            'suffix',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => true, 'primary' => false],
            'Suffix'
        )->addColumn(
            'start_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'primary' => false, 'default' => 1],
            'Start value for sequence'
        )->addColumn(
            'step',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'primary' => false, 'default' => 1],
            'Step for sequence'
        )->addColumn(
            'max_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'primary' => false],
            'MaxValue for sequence'
        )->addColumn(
            'warning_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'primary' => false],
            'WarningValue for sequence'
        )->addColumn(
            'is_active',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'isActive flag'
        )->addIndex(
            $installer->getIdxName(
                'sales_sequence_profile',
                ['meta_id', 'prefix', 'suffix'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE,
                '',
                self::$connectionName
            ),
            ['meta_id', 'prefix', 'suffix'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                'sales_sequence_profile',
                'meta_id',
                'sales_sequence_meta',
                'meta_id',
                self::$connectionName
            ),
            'meta_id',
            $installer->getTable('sales_sequence_meta', self::$connectionName),
            'meta_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection(self::$connectionName)->createTable($table);

        /**
         * Create table 'sales_sequence_meta'
         */
        $table = $installer->getConnection(self::$connectionName)->newTable(
            $installer->getTable('sales_sequence_meta', self::$connectionName)
        )->addColumn(
            'meta_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'entity_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false, 'primary' => false],
            'Prefix'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => false],
            'Store Id'
        )->addColumn(
            'sequence_table',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'table for sequence'
        )->addIndex(
            $installer->getIdxName(
                'sales_sequence_meta',
                ['entity_type', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE,
                '',
                self::$connectionName
            ),
            ['entity_type', 'store_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $installer->getConnection(self::$connectionName)->createTable($table);
        $installer->endSetup();
    }
}
