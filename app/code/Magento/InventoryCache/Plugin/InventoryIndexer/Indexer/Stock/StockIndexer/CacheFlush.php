<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCache\Plugin\InventoryIndexer\Indexer\Stock\StockIndexer;

use Magento\InventoryCache\Model\FlushCacheByTags;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

/**
 * Clean cache after non default stock reindex.
 */
class CacheFlush
{
    /**
     * @var FlushCacheByTags
     */
    private $flushCache;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * CacheInvalidate constructor.
     *
     * @param FlushCacheByTags $flushCache
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        FlushCacheByTags $flushCache,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->flushCache = $flushCache;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Clean cache after non default stock reindex.
     *
     * @param StockIndexer $subject
     * @param \Closure $proceed
     * @param array $stockIds
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteList(StockIndexer $subject, \Closure $proceed, array $stockIds)
    {
        $proceed($stockIds);
        unset($stockIds[$this->defaultStockProvider->getId()]);
        if ($stockIds) {
            $this->flushCache->execute();
        }
    }
}
