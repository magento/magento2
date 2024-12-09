<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
// phpcs:ignoreFile

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
     *
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
                'Smallint'
            )
            ->setComment('Reference table')
            ->setOption('charset', 'utf8mb4')
            ->setOption('collate', 'utf8mb4_general_ci');
        $installer->getConnection()->createTable($table);

        $testTable = $installer->getConnection()->newTable($installer->getTable('test_table'))
            ->addColumn(
                'smallint',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                2,
                ['nullable' => true, 'default' => 0],
                'Smallint'
            )
            ->addColumn(
                'bigint',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                10,
                ['nullable' => true, 'unsigned' => false, 'default' => 0],
                'Bigint'
            )
            ->addColumn(
                'float',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                null,
                ['default' => 0],
                'Float'
            )
            ->addColumn(
                'date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Date'
            )
            ->addColumn(
                'timestamp',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Timestamp'
            )
            ->addColumn(
                'mediumtext',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                11222222,
                [],
                'Mediumtext'
            )
            ->addColumn(
                'varchar',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                254,
                ['nullable' => true],
                'Varchar'
            )
            ->addColumn(
                'boolean',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                1,
                [],
                'Boolean'
            )
            ->addIndex(
                $installer->getIdxName('test_table', ['smallint', 'bigint']),
                ['smallint', 'bigint'],
                ['type' => \Magento\Framework\DB\Adapter\Pdo\Mysql::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('test_table', ['bigint']),
                ['bigint']
            )
            ->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('test_table'),
                    'smallint',
                    'reference_table',
                    'smallint_ref'
                ),
                'smallint',
                $installer->getTable('reference_table'),
                'smallint_ref',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Test Table')
            ->setOption('charset', 'utf8mb4')
            ->setOption('collate', 'utf8mb4_general_ci');
        $installer->getConnection()->createTable($testTable);

        $installer->endSetup();
    }
}
