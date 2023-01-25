<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Model\ResourceModel;

use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
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
    private $cache;

    /**
     * @var bool
     */
    private $isCacheEnabled;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param CacheInterface $cache
     * @param StateInterface $cacheState
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cache,
        StateInterface $cacheState,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->isCacheEnabled = $cacheState->isEnabled(Type::TYPE_IDENTIFIER);
        $this->serializer = $serializer;
    }

    /**
     * Cache attribute used in listing.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Config $config
     * @param callable $proceed
     * @return array
     */
    public function aroundGetAttributesUsedInListing(
        \Magento\Catalog\Model\ResourceModel\Config $config,
        callable $proceed
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
                    Type::CACHE_TAG,
                    Attribute::CACHE_TAG
                ]
            );
        }
        return $attributes;
    }

    /**
     * Cache attributes used for sorting.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Config $config
     * @param callable $proceed
     * @return array
     */
    public function aroundGetAttributesUsedForSortBy(
        \Magento\Catalog\Model\ResourceModel\Config $config,
        callable $proceed
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
                    Type::CACHE_TAG,
                    Attribute::CACHE_TAG
                ]
            );
        }
        return $attributes;
    }
}
