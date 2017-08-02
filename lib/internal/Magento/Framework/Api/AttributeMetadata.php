<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Base data object for custom attribute metadata
 * @since 2.0.0
 */
class AttributeMetadata extends AbstractSimpleObject implements MetadataObjectInterface
{
    const ATTRIBUTE_CODE = 'attribute_code';

    /**
     * Retrieve code of the attribute.
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * Set code of the attribute.
     *
     * @param string $attributeCode
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }
}
