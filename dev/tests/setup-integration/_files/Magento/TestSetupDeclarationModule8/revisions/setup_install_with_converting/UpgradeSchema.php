<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestSetupDeclarationModule8\Setup;

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
     * The name of the main table of Module8.
     */
    const UPDATE_TABLE = 'module8_test_update_table';

    /**
     * @inheritdoc
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $tableName = $setup->getTable(self::UPDATE_TABLE);
            $table = $setup->getConnection()->newTable($tableName);

            $this->addColumns($setup, $table);
            $this->addIndexes($table);
            $this->addConstraints($table);
            $setup->getConnection()->createTable($table);
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
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Primary Key'
            )
            ->addColumn(
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

}
