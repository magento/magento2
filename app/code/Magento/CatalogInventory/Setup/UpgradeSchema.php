<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;

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

        $foreignKeys = $this->getForeignKeys($setup, $oldCompositeKeyColumns);
        // drop foreign keys
        $this->dropForeignKeys($setup, $foreignKeys);

        $oldIndexName = $setup->getIdxName(
            $setup->getTable(StockItem::ENTITY),
            $oldCompositeKeyColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        $newIndexName = $setup->getIdxName(
            $setup->getTable(StockItem::ENTITY),
            $newCompositeKeyColumns,
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );

        // Drop a key based on the following columns: "product_id","website_id"
        $setup->getConnection()->dropIndex($setup->getTable(StockItem::ENTITY), $oldIndexName);

        // Create a key based on the following columns: "product_id","stock_id"
        $setup->getConnection()
            ->addIndex(
                $setup->getTable(StockItem::ENTITY),
                $newIndexName,
                $newCompositeKeyColumns,
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );
        // restore deleted foreign keys
        $this->createForeignKeys($setup, $foreignKeys);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array                $keys
     * @return void
     */
    private function dropForeignKeys(SchemaSetupInterface $setup, array $keys)
    {
        foreach ($keys as $key) {
            $setup->getConnection()->dropForeignKey($key['TABLE_NAME'], $key['FK_NAME']);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array                $keys
     * @return void
     */
    private function createForeignKeys(SchemaSetupInterface $setup, array $keys)
    {
        foreach ($keys as $key) {
            $setup->getConnection()->addForeignKey(
                $key['FK_NAME'],
                $key['TABLE_NAME'],
                $key['COLUMN_NAME'],
                $key['REF_TABLE_NAME'],
                $key['REF_COLUMN_NAME'],
                $key['ON_DELETE']
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param array                $compositeKeys
     * @return array
     */
    private function getForeignKeys(SchemaSetupInterface $setup, array $compositeKeys)
    {
        $foreignKeys = [];
        $allForeignKeys = $setup->getConnection()->getForeignKeys($setup->getTable(StockItem::ENTITY));
        foreach ($allForeignKeys as $key) {
            if (in_array($key['COLUMN_NAME'], $compositeKeys)) {
                $foreignKeys[] = $key;
            }
        }

        return $foreignKeys;
    }
}
