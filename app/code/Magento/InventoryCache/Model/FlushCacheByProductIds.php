<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Model;

use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Indexer\CacheContext;

/**
 * Clean cache for given product ids.
 */
class FlushCacheByProductIds
{
    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var string
     */
    private $productCacheTag;

    /**
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     * @param string $productCacheTag
     */
    public function __construct(
        CacheContext $cacheContext,
        EventManager $eventManager,
        string $productCacheTag
    ) {
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->productCacheTag = $productCacheTag;
    }

    /**
     * Clean cache for given product ids.
     *
     * @param array $productIds
     * @return void
     */
    public function execute(array $productIds)
    {
        if ($productIds) {
            $this->cacheContext->registerEntities($this->productCacheTag, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }
    }
}
