<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function get($key)
    {
        return parent::get($key);
    }
}
