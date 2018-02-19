<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryBundleIndexer\Indexer;

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
     * @param string $bundleSku
     * @return array
     */
    public function execute(array $bundleSourceItemsSkus, int $stockId, string $bundleSku): array
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
            'source_item.' . IndexStructure::SKU . ' in (?)', $bundleSourceItemsSkus
        );

        $indexData[IndexStructure::SKU] = $bundleSku;
        $indexData[IndexStructure::QUANTITY] = 0;
        $indexData = array_merge($indexData, $select->query()->fetch());

        return $indexData;
    }
}
