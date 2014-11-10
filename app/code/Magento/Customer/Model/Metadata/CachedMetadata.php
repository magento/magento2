<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model\Metadata;

use \Magento\Customer\Api\MetadataInterface;

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
     * @var \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
     */
    protected $allAttributeMetadataCache = null;

    /**
     * @var \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
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
