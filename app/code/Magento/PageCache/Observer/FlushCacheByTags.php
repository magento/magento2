<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class to flush cache by tags
 */
class FlushCacheByTags implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\PageCache\Cache
     *
     * @deprecated 100.1.0
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\PageCache\Model\Cache\Type
     */
    private $fullPageCache;

    /**
     * Invalidation tags resolver
     *
     * @var \Magento\Framework\App\Cache\Tag\Resolver
     */
    private $tagResolver;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Cache $cache
     * @param \Magento\Framework\App\Cache\Tag\Resolver $tagResolver
     * @param \Magento\PageCache\Model\Cache\Type $fullPageCache
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\PageCache\Cache $cache,
        \Magento\Framework\App\Cache\Tag\Resolver $tagResolver,
        \Magento\PageCache\Model\Cache\Type $fullPageCache
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->tagResolver = $tagResolver;
        $this->fullPageCache = $fullPageCache;
    }

    /**
     * Flushes cache
     *
     * If built-in caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->_config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if (!is_object($object)) {
                return;
            }
            $tags = $this->tagResolver->getTags($object);

            if (!empty($tags)) {
                $this->fullPageCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array_unique($tags));
            }
        }
    }
}
