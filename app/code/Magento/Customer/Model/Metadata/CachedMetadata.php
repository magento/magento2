<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\MetadataInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Cached attribute metadata service
 * @since 2.0.0
 */
class CachedMetadata implements MetadataInterface
{
    const CACHE_SEPARATOR = ';';

    /**
     * @var string
     * @since 2.1.0
     */
    protected $entityType = 'none';

    /**
     * @var AttributeMetadataCache
     * @since 2.2.0
     */
    private $attributeMetadataCache;

    /**
     * @var MetadataInterface
     * @since 2.0.0
     */
    protected $metadata;

    /**
     * Constructor
     *
     * @param MetadataInterface $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     * @since 2.0.0
     */
    public function __construct(
        MetadataInterface $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        $this->metadata = $metadata;
        $this->attributeMetadataCache = $attributeMetadataCache ?: ObjectManager::getInstance()
            ->get(AttributeMetadataCache::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAttributes($formCode)
    {
        $attributes = $this->attributeMetadataCache->load($this->entityType, $formCode);
        if ($attributes !== false) {
            return $attributes;
        }
        $attributes = $this->metadata->getAttributes($formCode);
        $this->attributeMetadataCache->save($this->entityType, $attributes, $formCode);
        return $attributes;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAttributeMetadata($attributeCode)
    {
        $attributesMetadata = $this->attributeMetadataCache->load($this->entityType, $attributeCode);
        if (false !== $attributesMetadata) {
            return array_shift($attributesMetadata);
        }
        $attributeMetadata = $this->metadata->getAttributeMetadata($attributeCode);
        $this->attributeMetadataCache->save($this->entityType, [$attributeMetadata], $attributeCode);
        return $attributeMetadata;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAllAttributesMetadata()
    {
        $attributes = $this->attributeMetadataCache->load($this->entityType, 'all');
        if ($attributes !== false) {
            return $attributes;
        }
        $attributes = $this->metadata->getAllAttributesMetadata();
        $this->attributeMetadataCache->save($this->entityType, $attributes, 'all');
        return $attributes;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        $attributes = $this->attributeMetadataCache->load($this->entityType, 'custom');
        if ($attributes !== false) {
            return $attributes;
        }
        $attributes = $this->metadata->getCustomAttributesMetadata();
        $this->attributeMetadataCache->save($this->entityType, $attributes, 'custom');
        return $attributes;
    }
}
