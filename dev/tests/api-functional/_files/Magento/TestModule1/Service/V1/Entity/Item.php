<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Service\V1\Entity;

class Item extends \Magento\Framework\Api\AbstractExtensibleObject
{
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
}
