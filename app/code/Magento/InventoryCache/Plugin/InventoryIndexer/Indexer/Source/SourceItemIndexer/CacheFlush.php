<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Plugin\InventoryIndexer\Indexer\Source\SourceItemIndexer;

use Magento\InventoryCache\Model\FlushCacheByProductIds;
use Magento\InventoryIndexer\Model\ResourceModel\GetProductIdsBySourceItemIds;
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
     * @param array $sourceItemIds
     * @param null $result
     * @throws \Exception in case catalog product entity type hasn't been initialize.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(SourceItemIndexer $subject, $result, array $sourceItemIds)
    {
        $productIds = $this->getProductIdsBySourceItemIds->execute($sourceItemIds);
        $this->flushCacheByIds->execute($productIds);
    }
}
