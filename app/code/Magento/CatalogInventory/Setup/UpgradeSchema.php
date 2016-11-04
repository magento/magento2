<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     */
    private $productCompositeKeyVersion = '2.2.0';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), $this->productCompositeKeyVersion, '<')) {
            $this->upgradeProductCompositeKey($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function upgradeProductCompositeKey(SchemaSetupInterface $setup)
    {
        $oldCompositeKeyColumns = ['product_id', 'website_id'];
        $newCompositeKeyColumns = ['product_id', 'stock_id'];

        $oldIndexName = $setup->getIdxName(
            \Magento\CatalogInventory\Model\Stock\Item::ENTITY,
            $oldCompositeKeyColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $newIndexName = $setup->getIdxName(
            \Magento\CatalogInventory\Model\Stock\Item::ENTITY,
            $newCompositeKeyColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        // Drop a key based on the following columns: "product_id","website_id"
        $setup->getConnection()->dropIndex(
            $setup->getTable(\Magento\CatalogInventory\Model\Stock\Item::ENTITY),
            $oldIndexName
        );

        // Create a key based on the following columns: "product_id","stock_id"
        $setup->getConnection()->addIndex(
            \Magento\CatalogInventory\Model\Stock\Item::ENTITY,
            $newIndexName,
            $newCompositeKeyColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
