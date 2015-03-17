<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\ObjectManagerInterface;

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
