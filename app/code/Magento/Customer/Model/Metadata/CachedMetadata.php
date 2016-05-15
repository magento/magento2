<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\MetadataInterface;
use Magento\Eav\Model\Entity\AttributeCache;
use Magento\Framework\App\ObjectManager;

/**
 * Cached attribute metadata service
 */
class CachedMetadata implements MetadataInterface
{
    const CACHE_SEPARATOR = ';';

    /**
     * @var string
     */
    protected $entityType = 'customer';

    /**
     * @var AttributeCache
     */
    private $cache;

    /**
     * @var MetadataInterface
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $attributeMetadataCache = [];

    /**
     * @var array
     */
    protected $attributesCache = [];

    /**
     * @var \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    protected $allAttributeMetadataCache = null;

    /**
     * @var \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    protected $customAttributesMetadataCache = null;

    /**
     * Initialize dependencies.
     *
     * @param MetadataInterface $metadata
     */
    public function __construct(MetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($formCode)
    {
        $key = $formCode;
        if (isset($this->attributesCache[$key])) {
            return $this->attributesCache[$key];
        }

        $value = $this->metadata->getAttributes($formCode);
        $this->attributesCache[$key] = $value;

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeMetadata($attributeCode)
    {
        $key = $attributeCode;
        if (isset($this->attributeMetadataCache[$key])) {
            return $this->attributeMetadataCache[$key];
        }

        $value = $this->metadata->getAttributeMetadata($attributeCode);
        $this->attributeMetadataCache[$key] = $value;

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributesMetadata()
    {
        if ($this->allAttributeMetadataCache !== null) {
            return $this->allAttributeMetadataCache;
        }
        $attributes = $this->getCache()->getAttributes($this->entityType, 'all');
        if ($attributes) {
            $this->allAttributeMetadataCache = $attributes;
            return $this->allAttributeMetadataCache;
        }


        $this->allAttributeMetadataCache = $this->metadata->getAllAttributesMetadata();
        $this->getCache()->saveAttributes($this->entityType, $this->allAttributeMetadataCache, 'all');
        return $this->allAttributeMetadataCache;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        if ($this->customAttributesMetadataCache !== null) {
            return $this->customAttributesMetadataCache;
        }
        $attributes = $this->getCache()->getAttributes($this->entityType, 'custom');
        if ($attributes) {
            $this->customAttributesMetadataCache = $attributes;
            return $this->customAttributesMetadataCache;
        }
        $this->customAttributesMetadataCache = $this->metadata->getCustomAttributesMetadata();
        $this->getCache()->saveAttributes($this->entityType, $attributes, 'custom');
        return $this->customAttributesMetadataCache;
    }

    /**
     * @return AttributeCache
     * @deprecated
     */
    protected function getCache()
    {
        if (!$this->cache) {
            $this->cache = ObjectManager::getInstance()->get(AttributeCache::class);
        }
       return $this->cache;
    }
}
