<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Service\V1\Entity;

class Item extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**#@+
     * Custom attribute code constants
     */
    const CUSTOM_ATTRIBUTE_1 = 'custom_attribute1';
    const CUSTOM_ATTRIBUTE_2 = 'custom_attribute2';
    const CUSTOM_ATTRIBUTE_3 = 'custom_attribute3';
    /**#@-*/

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->_data['item_id'];
    }

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        return $this->setData('item_id', $itemId);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_data['name'];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * Template method used to configure the attribute codes for the custom attributes
     *
     * @return string[]
     */
    protected function getCustomAttributesCodes()
    {
        return array_merge(
            parent::getCustomAttributesCodes(),
            [self::CUSTOM_ATTRIBUTE_1, self::CUSTOM_ATTRIBUTE_2, self::CUSTOM_ATTRIBUTE_3]
        );
    }
}
