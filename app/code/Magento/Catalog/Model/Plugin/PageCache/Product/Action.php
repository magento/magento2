<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Plugin\PageCache\Product;

use Magento\Catalog\Model\Product;
use Magento\Indexer\Model\CacheContext;
use Magento\Framework\Event\ManagerInterface as EventManager;

class Action
{
    /**
     * @var CacheContext
     */
    protected $cacheContext;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     */
    public function __construct(
        CacheContext $cacheContext,
        EventManager $eventManager
    ) {
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Product\Action $subject
     * @param \Closure $proceed
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return Product\Action
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundUpdateAttributes(
        Product\Action $subject,
        \Closure $proceed,
        $productIds,
        $attrData,
        $storeId
    ) {
        $returnValue = $proceed($productIds, $attrData, $storeId);

        $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);

        return $returnValue;
    }
}
