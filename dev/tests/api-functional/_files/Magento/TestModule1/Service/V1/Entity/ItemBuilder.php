<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule1\Service\V1\Entity;

class ItemBuilder extends \Magento\Framework\Api\ExtensibleObjectBuilder
{
    /**#@+
     * Custom attribute code constants
     */
    const CUSTOM_ATTRIBUTE_1 = 'custom_attribute1';
    const CUSTOM_ATTRIBUTE_2 = 'custom_attribute2';
    const CUSTOM_ATTRIBUTE_3 = 'custom_attribute3';
    /**#@-*/

    /**
     * @param int $itemId
     *
     * @return \Magento\TestModule1\Service\V1\Entity\ItemBuilder
     */
    public function setItemId($itemId)
    {
        $this->data['item_id'] = $itemId;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return \Magento\TestModule1\Service\V1\Entity\ItemBuilder
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Template method used to configure the attribute codes for the custom attributes
     *
     * @return string[]
     */
    public function getCustomAttributesCodes()
    {
        return array_merge(
            parent::getCustomAttributesCodes(),
            [self::CUSTOM_ATTRIBUTE_1, self::CUSTOM_ATTRIBUTE_2, self::CUSTOM_ATTRIBUTE_3]
        );
    }
}
