<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestSetupModule1\Setup;

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
         * Create table 'setup_table1'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('setup_tests_table1')
        )->addColumn(
            'column_with_type_boolean',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Column with type boolean'
        )->addColumn(
            'column_with_type_smallint',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Column with type smallint'
        )->addColumn(
            'column_with_type_integer',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'identity' => true, 'nullable' => false, 'primary' => true],
            'Column with type integer'
        )->addColumn(
            'column_with_type_bigint',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Column with type bigint'
        )->addColumn(
            'column_with_type_float',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            ['nullable' => true, 'default' => null],
            'Column with type float'
        )->addColumn(
            'column_with_type_numeric',
            \Magento\Framework\DB\Ddl\Table::TYPE_NUMERIC,
            '12,4',
            ['unsigned' => true, 'nullable' => true],
            'Column with type numeric'
        )->addColumn(
            'column_with_type_decimal',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['unsigned' => true, 'nullable' => true],
            'Column with type decimal'
        )->addColumn(
            'column_with_type_datetime',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null],
            'Column with type datetime'
        )->addColumn(
            'column_with_type_timestamp_update',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
            'Column with type timestamp update'
        )->addColumn(
            'column_with_type_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => true, 'default' => null],
            'Column with type date'
        )->addColumn(
            'column_with_type_text',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Column with type text'
        )->addColumn(
            'column_with_type_blob',
            \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
            32,
            [],
            'Column with type blob'
        )->addColumn(
            'column_with_type_verbinary',
            \Magento\Framework\DB\Ddl\Table::TYPE_VARBINARY,
            '2m',
            [],
            'Column with type varbinary'
        )->addIndex(
            $installer->getIdxName(
                'setup_tests_table1',
                ['column_with_type_text'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['column_with_type_text'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->addIndex(
            $installer->getIdxName(
                'setup_tests_table1',
                'column_with_type_integer',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            ),
            'column_with_type_integer',
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
        );

        $installer->getConnection()->createTable($table);

        $relatedTable = $installer->getConnection()->newTable(
            $installer->getTable('setup_tests_table1_related')
        )->addColumn(
            'column_with_type_timestamp_init_update',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'Column with type timestamp init update'
        )->addColumn(
            'column_with_type_timestamp_init',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Column with type timestamp init'
        )->addColumn(
            'column_with_relation',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Column with type integer and relation'
        )->addForeignKey(
            $installer->getFkName(
                'setup_table1_related',
                'column_with_relation',
                'setup_tests_table1',
                'column_with_type_integer'
            ),
            'column_with_relation',
            $installer->getTable('setup_tests_table1'),
            'column_with_type_integer',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Related Table'
        );
        $installer->getConnection()->createTable($relatedTable);
        $installer->endSetup();
    }
}
