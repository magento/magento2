<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Model;

use Magento\Framework\App\CacheInterface;
use Magento\PageCache\Model\Config;

/**
 * Clean cache for configured tags.
 */
class FlushCacheByTags
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Config
     */
    private $cacheConfig;

    /**
     * @var PurgeVarnishCacheByTags
     */
    private $purgeVarnishCacheByTags;

    /**
     * @var array
     */
    private $tags;

    /**
     * FlushCacheByTags constructor.
     *
     * @param Config $cacheConfig
     * @param CacheInterface $cache
     * @param PurgeVarnishCacheByTags $purgeVarnishCacheByTags
     * @param array $tags
     */
    public function __construct(
        Config $cacheConfig,
        CacheInterface $cache,
        PurgeVarnishCacheByTags $purgeVarnishCacheByTags,
        array $tags
    ) {
        $this->cache = $cache;
        $this->tags = $tags;
        $this->cacheConfig = $cacheConfig;
        $this->purgeVarnishCacheByTags = $purgeVarnishCacheByTags;
    }

    /**
     * Clean cache for predefined tags.
     *
     * @return void
     */
    public function execute()
    {
        if ($this->cacheConfig->isEnabled()) {
            switch ($this->cacheConfig->getType()) {
                case Config::BUILT_IN:
                    $this->cache->clean($this->tags);
                    break;
                case Config::VARNISH:
                    $this->purgeVarnishCacheByTags->execute($this->tags);
                    break;
            }
        }
    }
}
