<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestSetupDeclarationModule9\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install schema script for the TestSetupDeclarationModule9 module.
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * The name of the main table of Module9.
     */
    const MAIN_TABLE = 'module9_test_main_table';

    /**
     * @inheritdoc
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createTables($setup);

        $setup->endSetup();
    }

    /**
     * Create tables.
     *
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function createTables(SchemaSetupInterface $installer)
    {
        $mainTableName = $installer->getTable(self::MAIN_TABLE);
        $this->dropTableIfExists($installer, $mainTableName);
        $mainTable = $installer->getConnection()->newTable($mainTableName);
        $mainTable->setComment('Main Test Table for Module9');
        $this->addColumnsToMainTable($mainTable);
        $this->addIndexesToMainTable($mainTable);
        $installer->getConnection()->createTable($mainTable);
    }

    /**
     * Drop existing tables.
     *
     * @param SchemaSetupInterface $installer
     * @param string $table
     */
    private function dropTableIfExists($installer, $table)
    {
        $connection = $installer->getConnection();
        if ($connection->isTableExists($installer->getTable($table))) {
            $connection->dropTable(
                $installer->getTable($table)
            );
        }
    }

    /**
     * Add tables to main table.
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addColumnsToMainTable($table)
    {
        $table
            ->addColumn(
                'module9_email_contact_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'primary' => true,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Entity ID'
            )->addColumn(
                'module9_is_guest',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Is Guest'
            )->addColumn(
                'module9_guest_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true
                ],
                'Guest ID'
            )
            ->addColumn(
                'module9_created_at',
                Table::TYPE_DATE,
                null,
                [],
                'Created At'
            );
    }

    /**
     * Add indexes to main table.
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addIndexesToMainTable($table)
    {
        $table
            ->addIndex(
                'MODULE9_INSTALL_UNIQUE_INDEX_1',
                ['module9_email_contact_id', 'module9_guest_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
    }
}
