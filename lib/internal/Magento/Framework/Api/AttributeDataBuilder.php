<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;

/**
 * Custom Attribute Data object builder
 */
class AttributeDataBuilder extends AbstractSimpleObjectBuilder
{
    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->_set(AttributeValue::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Set attribute value
     *
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        return $this->_set(AttributeValue::VALUE, $value);
    }

    /**
     * Return the Data type class name
     *
     * @return string
     */
    protected function _getDataObjectType()
    {
        return '\Magento\Framework\Api\AttributeValue';
    }
}
