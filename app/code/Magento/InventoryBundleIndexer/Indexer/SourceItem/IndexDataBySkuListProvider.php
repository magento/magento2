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
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolver;

/**
 * Prepare index data for bundles products.
 */
class IndexDataBySkuListProvider
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
     * @param int $stockId
     * @param array $bundleChildrenSourceItemsSkus
     * @return ArrayIterator
     */
    public function execute(int $stockId, array $bundleChildrenSourceItemsSkus): ArrayIterator
    {
        $stockIndexTable = $this->stockIndexTableNameResolver->execute($stockId);

        $indexData = [];
        foreach ($bundleChildrenSourceItemsSkus as $bundleSku => $bundleChildrenSourceItems) {
            $select = $this->resourceConnection->getConnection()->select();
            $select->from(
                ['stock_index' => $stockIndexTable],
                [IndexStructure::IS_SALABLE => 'MAX(stock_index.' . IndexStructure::IS_SALABLE . ')']
            )->joinInner(
                ['source_item' => $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                'stock_index.' . IndexStructure::SKU . ' = source_item.' . SourceItemInterface::SKU,
                []
            )->where('source_item.' . SourceItemInterface::SKU . ' IN (?)', $bundleChildrenSourceItems);

            $isSalable = $this->resourceConnection->getConnection()->fetchOne($select);

            $indexData[] = [
                IndexStructure::SKU => $bundleSku,
                IndexStructure::QUANTITY => 0,
                IndexStructure::IS_SALABLE => $isSalable,
            ];
        }
        return new ArrayIterator($indexData);
    }
}
