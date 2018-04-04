<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCache\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\Indexer\CacheContext;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;

/**
 * Clean cache for given product ids.
 */
class FlushCacheByProductIds
{
    /**
     * @var Resolver
     */
    private $tagResolver;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var Type
     */
    private $fullPageCache;

    /**
     * @var Config
     */
    private $cacheConfig;

    /**
     * FlushCacheByProductIds constructor.
     *
     * @param Type $fullPageCache
     * @param Config $cacheConfig
     * @param CacheContext $cacheContext
     * @param Resolver $tagResolver
     */
    public function __construct(
        Type $fullPageCache,
        Config $cacheConfig,
        CacheContext $cacheContext,
        Resolver $tagResolver
    ) {
        $this->tagResolver = $tagResolver;
        $this->cacheContext = $cacheContext;
        $this->fullPageCache = $fullPageCache;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * Clean cache for given product ids.
     *
     * @param array $productIds
     * @return void
     */
    public function execute(array $productIds)
    {
        if ($this->cacheConfig->getType() == Config::BUILT_IN && $this->cacheConfig->isEnabled()) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $tags = $this->tagResolver->getTags($this->cacheContext);
            if ($tags) {
                $this->fullPageCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array_unique($tags));
            }
        }
    }
}
