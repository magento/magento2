<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

use Magento\Framework\Api\AbstractExtensibleObject;

class DataArray extends AbstractExtensibleObject
{
    /**
     * @return \Magento\Webapi\Service\Entity\Simple[]|null
     */
    public function getItems()
    {
        return $this->_get('items');
    }

    /**
     * @param \Magento\Webapi\Service\Entity\Simple[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this->setData('items', $items);
    }
}
