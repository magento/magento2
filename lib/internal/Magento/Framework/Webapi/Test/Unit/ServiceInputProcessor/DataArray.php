<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Api\AbstractExtensibleObject;

class DataArray extends AbstractExtensibleObject
{
    /**
     * @return \Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor\Simple[]|null
     */
    public function getItems()
    {
        return $this->_get('items');
    }

    /**
     * @param \Magento\Webapi\Service\Entity\Simple[] $items
     * @return $this
     */
    public function setItems(?array $items = null)
    {
        return $this->setData('items', $items);
    }
}
