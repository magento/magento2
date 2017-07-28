<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Custom Attribute Data object
 * @since 2.0.0
 */
class AttributeValue extends AbstractSimpleObject implements AttributeInterface
{
    /**
     * Get attribute code
     *
     * @return string
     * @since 2.0.0
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * Get attribute value
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeCode($attributeCode)
    {
        $this->_data[self::ATTRIBUTE_CODE] = $attributeCode;
        return $this;
    }

    /**
     * Set attribute value
     *
     * @param mixed $value
     * @return $this
     * @since 2.0.0
     */
    public function setValue($value)
    {
        $this->_data[self::VALUE] = $value;
        return $this;
    }
}
