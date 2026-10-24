<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
    public function setIds(?array $ids = null)
    {
        return $this->setData('ids', $ids);
    }
}
