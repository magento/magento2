<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestSetupDeclarationModule8\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install schema script for the TestSetupDeclarationModule8 module.
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * The name of the main table of Module8.
     */
    const MAIN_TABLE = 'module8_test_main_table';

    /**
     * The name of the second table of Module8.
     */
    const SECOND_TABLE = 'module8_test_second_table';

    /**
     * The name of the second table of Module8.
     */
    const TEMP_TABLE = 'module8_test_install_temp_table';

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
        $mainTable->setComment('Main Test Table for Module8');
        $this->addColumnsToMainTable($mainTable);
        $this->addIndexesToMainTable($mainTable);
        $installer->getConnection()->createTable($mainTable);

        $secondTableName = $installer->getTable(self::SECOND_TABLE);
        $this->dropTableIfExists($installer, $secondTableName);
        $secondTable = $installer->getConnection()->newTable($secondTableName);
        $secondTable->setComment('Second Test Table for Module8');
        $this->addColumnsToSecondTable($secondTable);
        $this->addIndexesToSecondTable($secondTable);
        $this->addConstraintsToSecondTable($secondTable);
        $installer->getConnection()->createTable($secondTable);

        $this->createSimpleTable($installer, self::TEMP_TABLE);
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
                'module8_email_contact_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'primary' => true,
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Email Contact ID'
            )->addColumn(
                'module8_contact_group_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Contact Group ID'
            )->addColumn(
                'module8_is_guest',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Is Guest'
            )->addColumn(
                'module8_contact_id',
                Table::TYPE_TEXT,
                15,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Contact ID'
            )->addColumn(
                'module8_content',
                Table::TYPE_TEXT,
                15,
                [
                    'nullable' => false,
                ],
                'Content'
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
                'MODULE8_INSTALL_INDEX_1',
                ['module8_email_contact_id']
            )->addIndex(
                'MODULE8_INSTALL_UNIQUE_INDEX_2',
                ['module8_email_contact_id', 'module8_is_guest'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addIndex(
                'MODULE8_INSTALL_INDEX_3',
                ['module8_is_guest']
            )->addIndex(
                'MODULE8_INSTALL_INDEX_4',
                ['module8_contact_id']
            )->addIndex(
                'MODULE8_INSTALL_INDEX_TEMP',
                ['module8_content']
            )->addIndex(
                'MODULE8_INSTALL_UNIQUE_INDEX_TEMP',
                ['module8_contact_group_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            );
    }

    /**
     * Add tables to second table.
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addColumnsToSecondTable($table)
    {
        $table
            ->addColumn(
                'module8_entity_id',
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
                'module8_contact_id',
                Table::TYPE_INTEGER,
                null,
                [],
                'Contact ID'
            )->addColumn(
                'module8_address',
                Table::TYPE_TEXT,
                15,
                [
                    'nullable' => false,
                ],
                'Address'
            )->addColumn(
                'module8_counter_with_multiline_comment',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => 0
                ],
                'Empty
                Counter
                Multiline
                Comment'
            )->addColumn(
                'module8_second_address',
                Table::TYPE_TEXT,
                15,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Second Address'
            )->addColumn(
                'module8_temp_column',
                Table::TYPE_TEXT,
                15,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Temp column for remove'
            );
    }

    /**
     * Add indexes to second table.
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addIndexesToSecondTable($table)
    {
        $table
            ->addIndex(
                'MODULE8_INSTALL_SECOND_TABLE_INDEX_1',
                ['module8_entity_id']
            )->addIndex(
                'MODULE8_INSTALL_SECOND_TABLE_INDEX_2',
                ['module8_address']
            )->addIndex(
                'MODULE8_INSTALL_SECOND_TABLE_INDEX_3_TEMP',
                ['module8_second_address']
            );
    }

    /**
     * Add constraints to second table.
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addConstraintsToSecondTable($table)
    {
        $table
            ->addForeignKey(
                'MODULE8_INSTALL_FK_ENTITY_ID_TEST_MAIN_TABLE_EMAIL_CONTACT_ID',
                'module8_entity_id',
                self::MAIN_TABLE,
                'module8_email_contact_id'
            )->addForeignKey(
                'MODULE8_INSTALL_FK_ADDRESS_TEST_MAIN_TABLE_CONTACT_ID',
                'module8_address',
                self::MAIN_TABLE,
                'module8_contact_id'
            )->addForeignKey(
                'MODULE8_INSTALL_FK_ADDRESS_TEST_MAIN_TABLE_MODULE8_CONTENT_TEMP',
                'module8_address',
                self::MAIN_TABLE,
                'module8_content'
            );
    }

    /**
     * Create a simple table.
     *
     * @param SchemaSetupInterface $setup
     * @param $tableName
     * @throws \Zend_Db_Exception
     */
    private function createSimpleTable(SchemaSetupInterface $setup, $tableName): void
    {
        $table = $setup->getConnection()->newTable($tableName);
        $table
            ->addColumn(
                'module8_entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'primary' => true,
                    'identity' => true,
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Entity ID'
            )->addColumn(
                'module8_counter',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => 100
                ],
                'Counter'
            );
        $setup->getConnection()->createTable($table);
    }
}
