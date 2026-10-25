<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent;

/**
 * Class ArrayObjectFactory
 */
class ArrayObjectFactory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \ArrayObject
     */
    public function create(array $data = [])
    {
        return new \ArrayObject($data);
    }
}
