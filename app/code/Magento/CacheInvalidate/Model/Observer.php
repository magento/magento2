<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Symfony\Component\Config\Definition\Exception\Exception;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\RequestInterface;

/**
 * Class Observer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Observer
{
    /**
     * Default port for purge requests
     */
    const DEFAULT_PORT = 80;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $config;

    /** @var PurgeCache */
    private $purgeCache;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param PurgeCache $purgeCache
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
    public function invalidateVarnish(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Framework\Object\IdentityInterface) {
                $tags = [];
                $pattern = "((^|,)%s(,|$))";
                foreach ($object->getIdentities() as $tag) {
                    $tags[] = sprintf($pattern, preg_replace("~_\\d+$~", '', $tag));
                    $tags[] = sprintf($pattern, $tag);
                }
                $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
            }
        }
    }

    /**
     * Flash Varnish cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function flushAllCache(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $this->purgeCache->sendPurgeRequest('.*');
        }
    }
}
