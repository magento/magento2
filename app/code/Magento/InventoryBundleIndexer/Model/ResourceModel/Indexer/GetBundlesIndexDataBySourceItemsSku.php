<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Model\ResourceModel\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolver;

/**
 * Prepare index data for bundle product.
 */
class GetBundlesIndexDataBySourceItemsSku
{
    /**
     * @var StockIndexTableNameResolver
     */
    private $stockIndexTableNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StockIndexTableNameResolver $stockIndexTableNameResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StockIndexTableNameResolver $stockIndexTableNameResolver
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param array $bundleSourceItemsSkus
     * @param int $stockId
     * @return array
     */
    public function execute(array $bundleSourceItemsSkus, int $stockId): array
    {
        $stockTable = $this->stockIndexTableNameResolver->execute($stockId);
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['stock' => $stockTable],
            []
        )->joinInner(
            ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
            'stock.sku = source_item.sku',
            []
        )->joinInner(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'source_item.sku = product.sku',
            ['product.sku', 'stock.quantity']
        )->joinInner(
            ['relation' => $this->resourceConnection->getTableName('catalog_product_relation')],
            'product.entity_id = relation.parent_id',
            []
        )->joinInner(
            ['child_product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'child_product.entity_id = relation.child_id',
            []
        )->joinInner(
            ['child_stock' => $stockTable],
            'child_stock.sku = child_product.sku',
            ['is_salable' => 'MAX(child_stock.' . IndexStructure::IS_SALABLE . ')']
        )
            ->where('source_item.' . IndexStructure::SKU . ' in (?)', $bundleSourceItemsSkus)
            ->group(IndexStructure::SKU);

        return $select->query()->fetchAll();
    }
}
