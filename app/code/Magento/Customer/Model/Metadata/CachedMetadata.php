<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\MetadataInterface;

/**
 * Cached attribute metadata service
 */
class CachedMetadata implements MetadataInterface
{
    const CACHE_SEPARATOR = ';';

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
        if (!is_null($this->allAttributeMetadataCache)) {
            return $this->allAttributeMetadataCache;
        }

        $this->allAttributeMetadataCache = $this->metadata->getAllAttributesMetadata();
        return $this->allAttributeMetadataCache;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        if (!is_null($this->customAttributesMetadataCache)) {
            return $this->customAttributesMetadataCache;
        }

        $this->customAttributesMetadataCache = $this->metadata->getCustomAttributesMetadata();
        return $this->customAttributesMetadataCache;
    }
}
