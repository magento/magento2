<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestSetupDeclarationModule1\Setup;

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

        //Create first table
        $table = $installer->getConnection()
            ->newTable($installer->getTable('reference_table'))
            ->addColumn(
                'smallint_ref',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['primary' => true, 'identity' => true, 'nullable' => false],
                'Value ID'
            )
            ->setComment('Reference table');
        $installer->getConnection()->createTable($table);

        $testTable = $installer->getConnection()->newTable('test_table')
            ->addColumn(
                'smallint',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                2,
                ['nullable' => true, 'default' => 0],
                'Value ID'
            )
            ->addColumn(
                'bigint',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                10,
                ['nullable' => true, 'unsigned' => false, 'default' => 0],
                'Value ID'
            )
            ->addColumn(
                'float',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                '12,0',
                ['default' => 0],
                'Value ID'
            )
            ->addColumn(
                'date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Value ID'
            )
            ->addColumn(
                'timestamp',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Value ID'
            )
            ->addColumn(
                'mediumtext',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                11222222,
                [],
                'Value ID'
            )
            ->addColumn(
                'varchar',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                254,
                ['nullable' => true],
                'Value ID'
            )
            ->addColumn(
                'boolean',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                1,
                [],
                'Value ID'
            )
            ->addIndex(
                'some_unique_key',
                ['smallint', 'bigint'],
                ['type' => \Magento\Framework\DB\Adapter\Pdo\Mysql::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                'speedup_index',
                ['bigint']
            )
            ->addForeignKey(
                'some_foreign_key',
                'smallint',
                $installer->getTable('reference_table'),
                'smallint_ref',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Test Table');
        $installer->getConnection()->createTable($testTable);

        $installer->endSetup();
    }
}
