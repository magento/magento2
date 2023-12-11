<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Observer;

use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\App\PageCache\Cache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;
use Zend_Cache;

/**
 * Observer used to cache by tags when using built-in full page cache
 */
class FlushCacheByTags implements ObserverInterface
{
    /**
     * @var Cache
     *
     * @deprecated 100.1.0
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var Config
     */
    protected $_config;

    /**
     * @var Type
     */
    private $fullPageCache;

    /**
     * Invalidation tags resolver
     *
     * @var Resolver
     */
    private $tagResolver;

    /**
     * @param Config $config
     * @param Cache $cache
     * @param Type $fullPageCache
     * @param Resolver $tagResolver
     */
    public function __construct(
        Config $config,
        Cache $cache,
        Type $fullPageCache,
        Resolver $tagResolver
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->fullPageCache = $fullPageCache;
        $this->tagResolver = $tagResolver;
    }

    /**
     * If Built-In caching is enabled it collects array of tags of incoming object and asks to clean cache.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->_config->getType() === Config::BUILT_IN && $this->_config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if (!is_object($object)) {
                return;
            }
            $tags = $this->tagResolver->getTags($object);

            if (!empty($tags)) {
                $this->fullPageCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array_unique($tags));
            }
        }
    }
}
