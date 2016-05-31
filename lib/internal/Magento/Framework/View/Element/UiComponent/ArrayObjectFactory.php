<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
