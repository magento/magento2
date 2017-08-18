<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class \Magento\Store\Setup\UpgradeSchema
 *
 */
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

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addCodeColumnToStoreGroupTable($setup);
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
                'length' => 32,
                'comment' => 'Store group unique code',
                'after' => 'website_id'
            ]
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
}
