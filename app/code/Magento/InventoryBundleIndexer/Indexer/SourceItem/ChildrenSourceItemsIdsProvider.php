<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Get bundle children source item ids by source item ids.
 *
 * Find product by source item sku and try find parent bundle product. Then find all children source
 * items for this bundle and return them with bundle sku.
 *
 * If source items is empty array so it returns all children source items.
 */
class ChildrenSourceItemsIdsProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $sourceItemsIds
     *
     * @return array
     */
    public function execute(array $sourceItemsIds = []): array
    {
        $select = $this->getChildrenSourceItemsIdsSelect();
        if ($sourceItemsIds) {
            $bundleIdsSelect = $this->getBundleIdsSelect($sourceItemsIds);
            $select->where('relation.parent_id IN (?)', $bundleIdsSelect);
        } else {
            $select->where('bundle_product.' . ProductInterface::TYPE_ID . ' = ?', ProductType::TYPE_BUNDLE);
        }

        $bundleChildren = $select->query()->fetchAll();
        $bundleChildrenSourceItemsIdsBySku = [];

        foreach ($bundleChildren as $bundleChild) {
            $bundleChildrenSourceItemsIdsBySku[$bundleChild['sku']][] = $bundleChild[SourceItem::ID_FIELD_NAME];
        }

        return $bundleChildrenSourceItemsIdsBySku;
    }

    /**
     * Get parent bundle ids by children source items ids.
     *
     * @param $sourceItemsIds
     * @return Select
     */
    private function getBundleIdsSelect($sourceItemsIds): Select
    {
        $productTable = $this->resourceConnection->getTableName('catalog_product_entity');
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
            []
        )->joinInner(
            ['product' => $productTable],
            'source_item.sku = product.sku',
            []
        )->joinInner(
            ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
            'product.entity_id = relation.child_id',
            []
        )->joinInner(
            ['bundle_product' => $productTable],
            'bundle_product.entity_id = relation.parent_id',
            ['bundle_product.entity_id']
        )->where(
            'source_item.' . SourceItem::ID_FIELD_NAME . ' IN (?)',
            $sourceItemsIds
        )->where(
            'bundle_product.' . ProductInterface::TYPE_ID . ' = ?',
            ProductType::TYPE_BUNDLE
        )->distinct(true);

        return $select;
    }

    /**
     * @return Select
     */
    private function getChildrenSourceItemsIdsSelect(): Select
    {
        $select = $this->resourceConnection->getConnection()->select();
        $select
            ->from(
                ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                [SourceItem::ID_FIELD_NAME]
            )->joinInner(
                ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'source_item.' . SourceItemInterface::SKU . ' = product.' . ProductInterface::SKU,
                []
            )->joinInner(
                ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
                'relation.child_id = product.entity_id',
                []
            )->joinInner(
                ['bundle_product' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'bundle_product.entity_id = relation.parent_id',
                ['bundle_product.sku']
            );

        return $select;
    }
}
