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
 */
class CachedMetadata implements MetadataInterface
{
    const CACHE_SEPARATOR = ';';

    /**
     * @var string
     */
    protected $entityType = 'none';

    /**
     * @var AttributeMetadataCache
     */
    private $attributeMetadataCache;

    /**
     * @var MetadataInterface
     */
    protected $metadata;

    /**
     * Constructor
     *
     * @param MetadataInterface $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        MetadataInterface $metadata,
        ?AttributeMetadataCache $attributeMetadataCache = null
    ) {
        $this->metadata = $metadata;
        $this->attributeMetadataCache = $attributeMetadataCache ?: ObjectManager::getInstance()
            ->get(AttributeMetadataCache::class);
    }

    /**
     * {@inheritdoc}
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
