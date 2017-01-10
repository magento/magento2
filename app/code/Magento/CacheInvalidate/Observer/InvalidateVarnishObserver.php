<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;

class InvalidateVarnishObserver implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\CacheInvalidate\Model\PurgeCache
     */
    protected $purgeCache;

    /**
     * Invalidation tags resolver
     *
     * @var \Magento\Framework\App\Cache\Tag\Resolver
     */
    private $tagResolver;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\CacheInvalidate\Model\PurgeCache $purgeCache
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\CacheInvalidate\Model\PurgeCache $purgeCache
    ) {
        $this->config = $config;
        $this->purgeCache = $purgeCache;
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        if (!is_object($object)) {
            return;
        }
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $bareTags = $this->getTagResolver()->getTags($object);

            $tags = [];
            $pattern = "((^|,)%s(,|$))";
            foreach ($bareTags as $tag) {
                $tags[] = sprintf($pattern, $tag);
            }
            if (!empty($tags)) {
                $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
            }

        }
    }

    /**
     * @deprecated
     * @return \Magento\Framework\App\Cache\Tag\Resolver
     */
    private function getTagResolver()
    {
        if ($this->tagResolver === null) {
            $this->tagResolver = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\App\Cache\Tag\Resolver::class);
        }
        return $this->tagResolver;
    }
}
