<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Observer;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer as Event;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type as PageCache;
use Magento\PageCache\Model\Config as CacheConfig;

/**
 * Flush the built in page cache when a category is moved
 */
class FlushCategoryPagesCache implements ObserverInterface
{

    /**
     * @var CacheConfig
     */
    private $cacheConfig;

    /**
     *
     * @var PageCache
     */
    private $pageCache;

    /**
     * FlushCategoryPagesCache constructor.
     *
     * @param CacheConfig $cacheConfig
     * @param PageCache $pageCache
     */
    public function __construct(CacheConfig $cacheConfig, PageCache $pageCache)
    {
        $this->cacheConfig = $cacheConfig;
        $this->pageCache = $pageCache;
    }

    /**
     * Clean the category page cache if built in cache page cache is used.
     *
     * The built in cache requires cleaning all pages that contain the top category navigation menu when a
     * category is moved. This is because the built in cache does not support ESI blocks.
     *
     * @param Event $event
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Event $event)
    {
        if ($this->cacheConfig->getType() == CacheConfig::BUILT_IN && $this->cacheConfig->isEnabled()) {
            $this->pageCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [Category::CACHE_TAG]);
        }
    }
}
