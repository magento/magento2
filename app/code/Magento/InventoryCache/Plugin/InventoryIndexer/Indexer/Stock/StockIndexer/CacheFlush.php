<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Plugin\InventoryIndexer\Indexer\Stock\StockIndexer;

use Magento\InventoryCache\Model\FlushCacheByProductIds;
use Magento\InventoryCache\Model\ResourceModel\GetProductIdsByStockIds;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

/**
 * Clean cache for specific products after non default stock reindex.
 */
class CacheFlush
{
    /**
     * @var FlushCacheByProductIds
     */
    private $flushCacheByProductIds;

    /**
     * @var GetProductIdsByStockIds
     */
    private $getProductIdsByStockIds;

    /**
     * @param FlushCacheByProductIds $flushCacheByProductIds
     * @param GetProductIdsByStockIds $getProductIdsForCacheFlush
     */
    public function __construct(
        FlushCacheByProductIds $flushCacheByProductIds,
        GetProductIdsByStockIds $getProductIdsForCacheFlush
    ) {
        $this->flushCacheByProductIds = $flushCacheByProductIds;
        $this->getProductIdsByStockIds = $getProductIdsForCacheFlush;
    }

    /**
     * Clean cache after non default stock reindex.
     *
     * @param StockIndexer $subject
     * @param callable $proceed
     * @param array $stockIds
     * @return void
     * @throws \Exception in case product entity type hasn't been initialize.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteList(StockIndexer $subject, callable $proceed, array $stockIds)
    {
        $beforeReindexProductIds = $this->getProductIdsByStockIds->execute($stockIds);
        $proceed($stockIds);
        $afterReindexProductIds = $this->getProductIdsByStockIds->execute($stockIds);
        $productIdsForCacheClean = array_diff($beforeReindexProductIds, $afterReindexProductIds);
        if ($productIdsForCacheClean) {
            $this->flushCacheByProductIds->execute($productIdsForCacheClean);
        }
    }
}
