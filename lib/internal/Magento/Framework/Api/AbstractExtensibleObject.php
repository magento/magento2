<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api;


/**
 * Base Class for extensible data Objects
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractExtensibleObject extends AbstractSimpleObject implements ExtensibleDataInterface
{
    /**
     * Array key for custom attributes
     */
    const CUSTOM_ATTRIBUTES_KEY = 'custom_attributes';

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null null if the attribute has not been set
     */
    public function getCustomAttribute($attributeCode)
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES])
            && isset($this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode])
                ? $this->_data[self::CUSTOM_ATTRIBUTES][$attributeCode]
                : null;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        return isset($this->_data[self::CUSTOM_ATTRIBUTES]) ? $this->_data[self::CUSTOM_ATTRIBUTES] : [];
    }
}
