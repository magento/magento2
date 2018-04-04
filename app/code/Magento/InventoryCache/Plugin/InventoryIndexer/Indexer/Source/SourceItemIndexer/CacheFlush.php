<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCache\Plugin\InventoryIndexer\Indexer\Source\SourceItemIndexer;

use Magento\InventoryCache\Model\FlushCacheByProductIds;
use Magento\InventoryCatalog\Model\GetProductIdsBySourceItemIds;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

/**
 * Clean cache for corresponding products after source item reindex.
 */
class CacheFlush
{
    /**
     * @var FlushCacheByProductIds
     */
    private $flushCacheByIds;

    /**
     * @var GetProductIdsBySourceItemIds
     */
    private $getProductIdsBySourceItemIds;

    /**
     * ProductCacheFlush constructor.
     *
     * @param FlushCacheByProductIds $flushCacheByIds
     * @param GetProductIdsBySourceItemIds $getProductIdsBySourceItemIds
     */
    public function __construct(
        FlushCacheByProductIds $flushCacheByIds,
        GetProductIdsBySourceItemIds $getProductIdsBySourceItemIds
    ) {
        $this->flushCacheByIds = $flushCacheByIds;
        $this->getProductIdsBySourceItemIds = $getProductIdsBySourceItemIds;
    }

    /**
     * Clean cache for specific products after source items reindex.
     *
     * @param SourceItemIndexer $subject
     * @param \Closure $proceed
     * @param array $sourceItemIds
     * @throws \Exception in case catalog product entity type hasn't been initialize.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteList(SourceItemIndexer $subject, \Closure $proceed, array $sourceItemIds)
    {
        $proceed($sourceItemIds);
        $productIds = $this->getProductIdsBySourceItemIds->execute($sourceItemIds);
        $this->flushCacheByIds->execute($productIds);
    }
}
