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
     * @var array
     */
    private $tags;

    /**
     * @var Config
     */
    private $cacheConfig;

    /**
     * FlushCacheByTags constructor.
     *
     * @param Config $cacheConfig
     * @param CacheInterface $cache
     * @param array $tags
     */
    public function __construct(
        Config $cacheConfig,
        CacheInterface $cache,
        array $tags
    ) {
        $this->cache = $cache;
        $this->tags = $tags;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * Clean cache for predefined tags.
     *
     * @return void
     */
    public function execute()
    {
        if ($this->cacheConfig->getType() == Config::BUILT_IN && $this->cacheConfig->isEnabled()) {
            $this->cache->clean($this->tags);
        }
    }
}
