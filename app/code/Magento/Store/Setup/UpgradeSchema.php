<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->addCodeColumnToStoreGroupTable($setup);
            $this->removeForeignKeys($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add new column 'code' to store_group table, and unique index for it.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addCodeColumnToStoreGroupTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('store_group'),
            'code',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'length' => 32,
                'comment' => 'Store group unique code'
            ]
        );
        $setup->getConnection()->update(
            $setup->getTable('store_group'),
            ['code' => new \Zend_Db_Expr('group_id')]
        );
        $setup->getConnection()->addIndex(
            $setup->getTable('store_group'),
            $setup->getIdxName(
                'store_group',
                ['code'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['code'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * Remove foreign keys from store and store_group tables.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeForeignKeys(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropForeignKey(
            $setup->getTable('store'),
            $setup->getConnection()->getForeignKeyName(
                'store',
                'website_id',
                'store_website',
                'website_id'
            )
        );

        $setup->getConnection()->dropForeignKey(
            $setup->getTable('store'),
            $setup->getConnection()->getForeignKeyName(
                'store',
                'group_id',
                'store_group',
                'group_id'
            )
        );

        $setup->getConnection()->dropForeignKey(
            $setup->getTable('store_group'),
            $setup->getConnection()->getForeignKeyName(
                'store_group',
                'website_id',
                'store_website',
                'website_id'
            )
        );
    }
}
