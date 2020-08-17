<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CacheInvalidate\Observer;

use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\PageCache\Model\Config;

/**
 * Observer used to invalidate varnish cache once Magento cache was cleaned
 */
class InvalidateVarnishObserver implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var PurgeCache
     */
    private $purgeCache;

    /**
     * Invalidation tags resolver
     *
     * @var Resolver
     */
    private $tagResolver;

    /**
     * @param Config $config
     * @param PurgeCache $purgeCache
     * @param Resolver $tagResolver
     */
    public function __construct(
        Config $config,
        PurgeCache $purgeCache,
        Resolver $tagResolver
    ) {
        $this->config = $config;
        $this->purgeCache = $purgeCache;
        $this->tagResolver = $tagResolver;
    }

    /**
     * If Varnish caching is enabled it collects array of tags of incoming object and asks to clean cache.
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

        if ((int)$this->config->getType() === Config::VARNISH && $this->config->isEnabled()) {
            $bareTags = $this->tagResolver->getTags($object);

            $tags = [];
            $pattern = '((^|,)%s(,|$))';
            foreach ($bareTags as $tag) {
                $tags[] = sprintf($pattern, $tag);
            }
            if (!empty($tags)) {
                $this->purgeCache->sendPurgeRequest(array_unique($tags));
            }
        }
    }
}
