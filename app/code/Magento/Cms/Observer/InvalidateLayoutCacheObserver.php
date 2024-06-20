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

namespace Magento\Cms\Observer;

use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Widget\Model\Widget\Instance;

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
     * @var Resolver
     */
    private $tagResolver;

    /**
     * @param LayoutCache $layoutCache
     * @param CacheState $cacheState
     * @param Resolver $tagResolver
     */
    public function __construct(
        LayoutCache $layoutCache,
        CacheState $cacheState,
        Resolver $tagResolver
    ) {
        $this->layoutCache = $layoutCache;
        $this->cacheState = $cacheState;
        $this->tagResolver = $tagResolver;
    }

    /**
     * Clean identities of event object from layout cache
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $object = $observer->getEvent()->getObject();

        if (!is_object($object)) {
            return;
        }

        if (!$this->cacheState->isEnabled(LayoutCache::TYPE_IDENTIFIER)) {
            return;
        }

        if (!$object->dataHasChangedFor(Page::PAGE_LAYOUT)) {
            return;
        }

        $tags = $this->tagResolver->getTags($object);

        if (!empty($tags)) {
            $tags[] = $this->getAdditionalTags($object);
            $this->layoutCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
        }
    }

    /**
     * Get additional tags
     */
    public function getAdditionalTags($object): string
    {
        $tag = '';
        if ($object instanceof Page) {
            $tag = sprintf(
                    '%s_%s',
                    'CMS_PAGE_VIEW_ID',
                    str_replace('-', '_', strtoupper($object->getIdentifier()))
                    );
        } elseif ($object instanceof Product) {
            $tag = sprintf(
                    '%s',
                    str_replace(
                        '{{ID}}',
                        (string) $object->getId(),
                        Instance::SINGLE_PRODUCT_LAYOUT_HANDLE
                    ),
                 );
        }
        return $tag;
    }
}
