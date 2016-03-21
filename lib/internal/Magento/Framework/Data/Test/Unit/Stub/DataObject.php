<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Stub;

use Magento\Framework\Data\AbstractDataObject;

class DataObject extends AbstractDataObject
{
    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return parent::get($key);
    }
}
