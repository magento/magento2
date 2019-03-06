<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestSetupDeclarationModule9\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\TestSetupDeclarationModule8\Setup\InstallSchema as Module8InstallSchema;
use Magento\TestSetupDeclarationModule8\Setup\UpgradeSchema as Module8UpgradeSchema;

/**
 * Upgrade schema script for the TestSetupDeclarationModule9 module.
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * The name of the main table of Module9.
     */
    const REPLICA_TABLE = 'module9_test_update_replica_table';

    /**
     * @inheritdoc
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addColumns($setup);
            $this->addIndexes($setup);
            $this->addConstraints($setup);
            $this->removeColumns($setup);
            $this->removeIndexes($setup);
            $this->removeConstraints($setup);
            $this->removeTables($setup);
            $replicaTable = $setup->getConnection()
                ->createTableByDdl(Module8InstallSchema::SECOND_TABLE, self::REPLICA_TABLE);
            $setup->getConnection()->createTable($replicaTable);
        }

        $setup->endSetup();
    }

    /**
     * Create columns for tables.
     *
     * @param SchemaSetupInterface $setup
     */
    private function addColumns(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            InstallSchema::MAIN_TABLE,
            'module9_update_column',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'comment' => 'Module_9 Update Column',
            ]
        );

        $setup->getConnection()->addColumn(
            Module8InstallSchema::MAIN_TABLE,
            'module9_update_column',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'comment' => 'Module_9 Update Column',
            ]
        );
    }

    /**
     * Add indexes.
     *
     * @param SchemaSetupInterface $setup
     */
    private function addIndexes(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()
            ->addIndex(
                Module8UpgradeSchema::UPDATE_TABLE,
                'MODULE9_UPDATE_MODULE8_GUEST_BROWSER_ID',
                [
                    'module8_guest_browser_id'
                ]
            );
    }

    /**
     * Add constraints.
     *
     * @param SchemaSetupInterface $setup
     */
    private function addConstraints(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()
            ->addForeignKey(
                'MODULE9_UPDATE_FK_MODULE9_IS_GUEST',
                InstallSchema::MAIN_TABLE,
                'module9_is_guest',
                Module8InstallSchema::MAIN_TABLE,
                'module8_is_guest',
                Table::ACTION_CASCADE
            );
    }

    /**
     * Remove columns.
     *
     * @param SchemaSetupInterface $setup
     */
    private function removeColumns(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()
            ->dropColumn(
                Module8UpgradeSchema::UPDATE_TABLE,
                'module8_column_for_remove'
            );
    }

    /**
     * Remove indexes.
     *
     * @param SchemaSetupInterface $setup
     */
    private function removeIndexes(SchemaSetupInterface $setup): void
    {
        $connection = $setup->getConnection();
        $connection
            ->dropIndex(
                Module8InstallSchema::SECOND_TABLE,
                'MODULE8_INSTALL_SECOND_TABLE_INDEX_3_TEMP'
            );
    }

    /**
     * Remove constraints.
     *
     * @param SchemaSetupInterface $setup
     */
    private function removeConstraints(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()
            ->dropForeignKey(
                Module8InstallSchema::SECOND_TABLE,
                'MODULE8_INSTALL_FK_ADDRESS_TEST_MAIN_TABLE_CONTACT_ID'
            )->dropIndex(
                Module8UpgradeSchema::UPDATE_TABLE,
                'MODULE8_UPDATE_UNIQUE_INDEX_TEMP'
            );
    }

    /**
     * Remove tables.
     *
     * @param SchemaSetupInterface $setup
     */
    private function removeTables(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()
            ->dropTable(
                Module8UpgradeSchema::TEMP_TABLE
            );
    }
}
