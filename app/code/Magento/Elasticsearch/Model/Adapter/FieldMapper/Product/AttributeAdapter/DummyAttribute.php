<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;

use Magento\Framework\Api\CustomAttributesDataInterface;

/**
 * Dummy class for Not EAV attribute.
 * @SuppressWarnings(PHPMD)
 */
class DummyAttribute implements CustomAttributesDataInterface
{
    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
     */
    public function getCustomAttribute($attributeCode)
    {
        return null;
    }

    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        return $this;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        return null;
    }

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this;
    }

    /**
     * Get property value that guarantee of using an attribute in sort purposes on the storefront.
     *
     * @return bool
     */
    public function getUsedForSortBy()
    {
        return false;
    }

    /**
     * Dummy attribute doesn't have backend type.
     *
     * @return null
     */
    public function getBackendType()
    {
        return null;
    }
}
