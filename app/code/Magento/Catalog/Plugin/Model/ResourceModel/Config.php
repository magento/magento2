<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Config cache plugin.
 */
class Config
{
    /**#@+
     * Product listing attributes cache ids
     */
    const PRODUCT_LISTING_ATTRIBUTES_CACHE_ID = 'PRODUCT_LISTING_ATTRIBUTES';
    const PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID = 'PRODUCT_LISTING_SORT_BY_ATTRIBUTES';
    /**#@-*/

    /**#@-*/
    protected $cache;

    /**
     * @var bool|null
     */
    protected $isCacheEnabled = null;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->isCacheEnabled = $cacheState->isEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Cache attribute used in listing.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Config $config
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetAttributesUsedInListing(
        \Magento\Catalog\Model\ResourceModel\Config $config,
        \Closure $proceed
    ) {
        $cacheId = self::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID . $config->getEntityTypeId() . '_' . $config->getStoreId();
        if ($this->isCacheEnabled && ($attributes = $this->cache->load($cacheId))) {
            return $this->serializer->unserialize($attributes);
        }
        $attributes = $proceed();
        if ($this->isCacheEnabled) {
            $this->cache->save(
                $this->serializer->serialize($attributes),
                $cacheId,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                ]
            );
        }
        return $attributes;
    }

    /**
     * Cache attributes used for sorting.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Config $config
     * @param \Closure $proceed
     * @return array
     */
    public function aroundGetAttributesUsedForSortBy(
        \Magento\Catalog\Model\ResourceModel\Config $config,
        \Closure $proceed
    ) {
        $cacheId = self::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID . $config->getEntityTypeId() . '_'
            . $config->getStoreId();
        if ($this->isCacheEnabled && ($attributes = $this->cache->load($cacheId))) {
            return $this->serializer->unserialize($attributes);
        }
        $attributes = $proceed();
        if ($this->isCacheEnabled) {
            $this->cache->save(
                $this->serializer->serialize($attributes),
                $cacheId,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
                ]
            );
        }
        return $attributes;
    }
}
