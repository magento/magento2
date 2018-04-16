<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Plugin\InventoryIndexer\Indexer\Stock\StockIndexer;

use Magento\InventoryCache\Model\FlushCacheByProductIds;
use Magento\InventoryCache\Model\ResourceModel\GetProductIdsForCacheFlush;
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
     * @var GetProductIdsForCacheFlush
     */
    private $getProductIdsForCacheFlush;

    /**
     * @param FlushCacheByProductIds $flushCacheByProductIds
     * @param GetProductIdsForCacheFlush $getProductIdsForCacheFlush
     */
    public function __construct(
        FlushCacheByProductIds $flushCacheByProductIds,
        GetProductIdsForCacheFlush $getProductIdsForCacheFlush
    ) {
        $this->flushCacheByProductIds = $flushCacheByProductIds;
        $this->getProductIdsForCacheFlush = $getProductIdsForCacheFlush;
    }

    /**
     * Clean cache after non default stock reindex.
     *
     * @param StockIndexer $subject
     * @param \Magento\Framework\MultiDimensionalIndexer\IndexName[] $indexNames
     * @return \Magento\Framework\MultiDimensionalIndexer\IndexName[]
     * @throws \Exception in case product entity type hasn't been initialize.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuildIndex(StockIndexer $subject, array $indexNames)
    {
        $productIds = $this->getProductIdsForCacheFlush->execute($indexNames);
        if ($productIds) {
            $this->flushCacheByProductIds->execute($productIds);
        }

        return $indexNames;
    }
}
