<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer\SourceItem;

use ArrayIterator;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolver;

/**
 * Prepare index data for bundle product.
 */
class BundleIndexDataProvider
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
     * @param array $bundleChildrenSourceItemsSkus
     * @param int $stockId
     * @param string $bundleSku
     * @return ArrayIterator
     */
    public function execute(array $bundleChildrenSourceItemsSkus, int $stockId, string $bundleSku): ArrayIterator
    {
        $stockTable = $this->stockIndexTableNameResolver->execute($stockId);
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['stock' => $stockTable],
            ['is_salable' => 'MAX(stock.' . IndexStructure::IS_SALABLE . ')']
        )->joinInner(
            ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
            'stock.sku = source_item.sku',
            []
        )->where(
            'source_item.' . IndexStructure::SKU . ' in (?)', $bundleChildrenSourceItemsSkus
        );

        $data = $select->query()->fetch();
        $isSalable = $data[IndexStructure::IS_SALABLE] ?: 0;

        $indexData[IndexStructure::SKU] = $bundleSku;
        $indexData[IndexStructure::QUANTITY] = 0;
        $indexData[IndexStructure::IS_SALABLE] = $isSalable;

        return new ArrayIterator($indexData);
    }
}
