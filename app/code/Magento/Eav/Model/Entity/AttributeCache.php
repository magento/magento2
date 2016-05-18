<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\StateInterface;

/**
 * Class AttributeCache
 */
class AttributeCache
{
    /** cache prefix */
    const ATTRIBUTES_CACHE_PREFIX = 'ATTRIBUTE_INSTANCES_CACHE';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StateInterface
     */
    private $state;

    /**
     * @var AbstractAttribute[][]
     */
    private $attributeInstances;

    /**
     * @var bool
     */
    private $isAttributeCacheEnabled;

    /**
     * @var array
     */
    private $unsupportedTypes;

    /**
     * AttributeCache constructor.
     * @param CacheInterface $cache
     * @param StateInterface $state
     * @param array $unsupportedTypes
     */
    public function __construct(
        CacheInterface $cache,
        StateInterface $state,
        $unsupportedTypes = []
    ) {
        $this->cache = $cache;
        $this->state = $state;
        $this->unsupportedTypes = $unsupportedTypes;
    }

    /**
     * @return bool
     */
    private function isAttributeCacheEnabled()
    {
        if ($this->isAttributeCacheEnabled === null) {
            $this->isAttributeCacheEnabled = $this->state->isEnabled(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER);
        }
        return $this->isAttributeCacheEnabled;
    }

    /**
     * Return attributes from cache
     *
     * @param string $entityType
     * @param string $suffix
     * @return object[]
     */
    public function getAttributes($entityType, $suffix = '')
    {
        if (in_array($entityType, $this->unsupportedTypes)) {
            return false;
        }
        if (isset($this->attributeInstances[$entityType . $suffix])) {
            return $this->attributeInstances[$entityType . $suffix];
        }
        if ($this->isAttributeCacheEnabled()) {
            $cacheKey = self::ATTRIBUTES_CACHE_PREFIX . $entityType . $suffix;
            $attributesData = $this->cache->load($cacheKey);
            if ($attributesData) {
                $attributes = unserialize($attributesData);
                $this->attributeInstances[$entityType . $suffix] = $attributes;
                return $attributes;
            }
        }
        return false;
    }

    /**
     * Save attributes to cache
     *
     * @param string $entityType
     * @param object[] $attributes
     * @param string $suffix
     * @return bool
     */
    public function saveAttributes($entityType, $attributes, $suffix = '')
    {
        if (in_array($entityType, $this->unsupportedTypes)) {
            return true;
        }
        $this->attributeInstances[$entityType . $suffix] = $attributes;
        if ($this->isAttributeCacheEnabled()) {
            $cacheKey = self::ATTRIBUTES_CACHE_PREFIX . $entityType . $suffix;
            $attributesData = serialize($attributes);
            $this->cache->save(
                $attributesData,
                $cacheKey,
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
                    \Magento\Framework\App\Config\ScopePool::CACHE_TAG
                ]
            );
        }
        return true;
    }

    /**
     * Clear attributes cache
     *
     * @return bool
     */
    public function clear()
    {
        unset($this->attributeInstances);
        if ($this->isAttributeCacheEnabled()) {
            $this->cache->clean(
                [
                    \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                    \Magento\Eav\Model\Entity\Attribute::CACHE_TAG,
                ]
            );
        }
        return true;
    }
}
