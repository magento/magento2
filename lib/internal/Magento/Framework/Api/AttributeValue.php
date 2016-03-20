<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Custom Attribute Data object
 */
class AttributeValue extends AbstractSimpleObject implements AttributeInterface
{
    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * Get attribute value
     *
     * @return mixed
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
     */
    public function setValue($value)
    {
        $this->_data[self::VALUE] = $value;
        return $this;
    }
}
