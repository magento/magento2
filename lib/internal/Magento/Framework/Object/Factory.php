<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Object;

class Factory
{
    /**
     * Create Magento object with provided params
     *
     * @param array $data
     * @return \Magento\Framework\Object
     */
    public function create(array $data = [])
    {
        return new \Magento\Framework\Object($data);
    }
}
