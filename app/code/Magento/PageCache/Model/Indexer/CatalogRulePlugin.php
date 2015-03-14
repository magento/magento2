<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Indexer;

/**
 * Class CatalogRulePlugin
 * @package Magento\PageCache\Model\Indexer
 */
class CatalogRulePlugin
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    protected $pool;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $pool
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\Type\FrontendPool $pool
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->pool = $pool;
    }

    /**
     * @param \Magento\CatalogRule\Model\Indexer\AbstractIndexer $subject
     *
     * @return \Magento\CatalogRule\Model\Indexer\AbstractIndexer
     */
    public function afterExecuteFull(
        \Magento\CatalogRule\Model\Indexer\AbstractIndexer $subject
    ) {
        if ($this->config->isEnabled()) {
            $this->pool->get(
                \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
            )->clean(
                \Zend_Cache::CLEANING_MODE_ALL,
                [
                    \Magento\Catalog\Model\Category::CACHE_TAG,
                    \Magento\Catalog\Model\Product::CACHE_TAG
                ]
            );

            $this->cache->clean(
                [
                    \Magento\Catalog\Model\Category::CACHE_TAG,
                    \Magento\Catalog\Model\Product::CACHE_TAG,
                    \Magento\Catalog\Model\Product\Compare\Item::CACHE_TAG,
                    \Magento\Wishlist\Model\Wishlist::CACHE_TAG
                ]
            );
        }
        return $subject;
    }
}
