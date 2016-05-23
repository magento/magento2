<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Api\AbstractExtensibleObject;

class SimpleArray extends AbstractExtensibleObject
{
    /**
     * @return int[]
     */
    public function getIds()
    {
        return $this->_get('ids');
    }

    /**
     * @param int[] $ids
     * @return $this
     */
    public function setIds(array $ids = null)
    {
        return $this->setData('ids', $ids);
    }
}
