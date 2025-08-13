<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Observer;

use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Theme\Model\LayoutCacheTagResolverFactory;

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
     * @var LayoutCacheTagResolverFactory
     */
    private $layoutCacheTagResolver;

    /**
     * @param LayoutCache $layoutCache
     * @param CacheState $cacheState
     * @param LayoutCacheTagResolverFactory $layoutCacheTagResolver
     */
    public function __construct(
        LayoutCache $layoutCache,
        CacheState $cacheState,
        LayoutCacheTagResolverFactory $layoutCacheTagResolver
    ) {
        $this->layoutCache = $layoutCache;
        $this->cacheState = $cacheState;
        $this->layoutCacheTagResolver = $layoutCacheTagResolver;
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
        $tagResolver = $this->layoutCacheTagResolver->getStrategy($object);

        if (!$tagResolver || !is_object($object)) {
            return;
        }

        if (!$this->cacheState->isEnabled(LayoutCache::TYPE_IDENTIFIER)) {
            return;
        }

        $tags = $tagResolver->getTags($object);

        if (!empty($tags)) {
            $this->layoutCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
        }
    }
}
