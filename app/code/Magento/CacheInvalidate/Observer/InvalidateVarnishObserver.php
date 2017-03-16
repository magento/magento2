<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Observer;

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
     * Batch size of the purge request.
     *
     * Based on default Varnish 4 http_req_hdr_len size minus a 512 bytes margin for method,
     * header name, line feeds etc.
     *
     * @see http://www.varnish-cache.org/docs/4.0/reference/varnishd.html#http-req-hdr-len
     *
     * @var int
     */
    private $requestSize = 7680;

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
     * For scaling reason the purge request is chopped down to fix batch size.
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

            $tagsBatchSize = 0;
            $tags = [];
            $pattern = '((^|,)%s(,|$))';
            foreach ($bareTags as $tag) {
                $formattedTag = sprintf($pattern, $tag);

                // Send request if batch size is reached and add the implode with pipe to the computation
                if ($tagsBatchSize + strlen($formattedTag) > $this->requestSize - count($tags) - 1) {
                    $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
                    $tags = [];
                    $tagsBatchSize = 0;
                }

                $tagsBatchSize += strlen($formattedTag);
                $tags[] = $formattedTag;
            }
            if (!empty($tags)) {
                $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
            }
        }
    }

    /**
     * @deprecated 100.1.2
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
