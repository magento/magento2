<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestSetupDeclarationModule8\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade schema script for the TestSetupDeclarationModule8 module.
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * The name of the main table of the Module8.
     */
    const UPDATE_TABLE = 'module8_test_update_table';

    /**
     * The name of the temporary table of the Module8.
     */
    const TEMP_TABLE = 'module8_test_temp_table';

    /**
     * @inheritdoc
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $tableName = $setup->getTable(self::UPDATE_TABLE);
            $table = $setup->getConnection()->newTable($tableName);
            $table->setComment('Update Test Table for Module8');

            $this->addColumns($setup, $table);
            $this->addIndexes($table);
            $this->addConstraints($table);
            $setup->getConnection()->createTable($table);

            $this->createSimpleTable($setup, $setup->getTable(self::TEMP_TABLE));
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $connection = $setup->getConnection();
            $connection
                ->dropTable(
                    InstallSchema::TEMP_TABLE
                );
            $connection
                ->dropColumn(
                    InstallSchema::SECOND_TABLE,
                    'module8_temp_column'
                );
            $connection
                ->dropForeignKey(
                    InstallSchema::SECOND_TABLE,
                    'MODULE8_INSTALL_FK_ADDRESS_TEST_MAIN_TABLE_MODULE8_CONTENT_TEMP'
                );
            $connection
                ->dropIndex(
                    InstallSchema::MAIN_TABLE,
                    'MODULE8_INSTALL_INDEX_TEMP'
                );
            $connection
                ->dropIndex(
                    InstallSchema::MAIN_TABLE,
                    'MODULE8_INSTALL_UNIQUE_INDEX_TEMP'
                );
        }

        $setup->endSetup();
    }

    /**
     * Create columns for tables.
     *
     * @param SchemaSetupInterface $setup
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addColumns(SchemaSetupInterface $setup, Table $table): void
    {
        $table
            ->addColumn(
                'module8_entity_id',
                Table::TYPE_INTEGER,
                10,
                [
                    'primary' => true,
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Entity ID'
            )->addColumn(
                'module8_entity_row_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ]
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
                'module8_guest_browser_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Guest Browser ID'
            )->addColumn(
                'module8_column_for_remove',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'For remove'
            );

        $setup->getConnection()->addColumn(
            InstallSchema::MAIN_TABLE,
            'module8_update_column',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'comment' => 'Module_8 Update Column',
            ]
        );
    }

    /**
     * Add indexes.
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addIndexes(Table $table): void
    {
        $table
            ->addIndex(
                'MODULE8_UPDATE_IS_GUEST_INDEX',
                [
                    'module8_is_guest'
                ]
            )->addIndex(
                'MODULE8_UPDATE_UNIQUE_INDEX_TEMP',
                [
                    'module8_entity_id',
                    'module8_is_guest',

                ],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addIndex(
                'MODULE8_UPDATE_TEMP_INDEX',
                [
                    'module8_column_for_remove',
                    'module8_guest_browser_id'
                ]
            );
    }

    /**
     * Add constraints.
     *
     * @param Table $table
     * @throws \Zend_Db_Exception
     */
    private function addConstraints(Table $table): void
    {
        $table
            ->addForeignKey(
                'MODULE8_UPDATE_FK_MODULE8_IS_GUEST',
                'module8_is_guest',
                InstallSchema::MAIN_TABLE,
                'module8_is_guest',
                Table::ACTION_CASCADE
            )->addForeignKey(
                'MODULE8_UPDATE_FK_TEMP',
                'module8_column_for_remove',
                InstallSchema::MAIN_TABLE,
                'module8_is_guest',
                Table::ACTION_CASCADE
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
