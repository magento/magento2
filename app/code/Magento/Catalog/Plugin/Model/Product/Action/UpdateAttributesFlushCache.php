<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\Product\Action;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface as EventManager;

class UpdateAttributesFlushCache
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
     * @param Action $subject
     * @param \Closure $proceed
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return Action
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundUpdateAttributes(
        Action $subject,
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
