<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @param array $stockIds
     * @param null $result
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(StockIndexer $subject, $result, array $stockIds)
    {
        unset($stockIds[$this->defaultStockProvider->getId()]);
        if ($stockIds) {
            $this->flushCache->execute();
        }
    }
}
