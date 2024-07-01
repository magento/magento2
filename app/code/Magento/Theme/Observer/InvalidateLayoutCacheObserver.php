<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Theme\Observer;

use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Cache\Tag\Strategy\Factory;

/**
 * Invalidates layout cache.
 */
class InvalidateLayoutCacheObserver implements ObserverInterface
{
    /**
     * @var LayoutCache
     */
    private $layoutCache;

    /**
     * @var CacheState
     */
    private $cacheState;

    /**
     * Tag strategies factory
     *
     * @var Factory
     */
    private $strategyFactory;

    /**
     * @param LayoutCache $layoutCache
     * @param CacheState $cacheState
     * @param Factory $factory
     */
    public function __construct(
        LayoutCache $layoutCache,
        CacheState $cacheState,
        Factory $factory
    ) {
        $this->layoutCache = $layoutCache;
        $this->cacheState = $cacheState;
        $this->strategyFactory = $factory;
    }

    /**
     * Clean identities of event object from layout cache
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $object = $observer->getEvent()->getObject();

        if (!is_object($object)) {
            return;
        }

        if (!$this->cacheState->isEnabled(LayoutCache::TYPE_IDENTIFIER)) {
            return;
        }

        $tags = $this->strategyFactory->getStrategy($object)->getTags($object);

        if (!empty($tags)) {
            $this->layoutCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
        }
    }
}
