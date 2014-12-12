<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
