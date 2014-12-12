<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class BasicInjection
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_object;

    /**
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $object
     */
    public function __construct(\Magento\Framework\ObjectManager\TestAsset\Basic $object)
    {
        $this->_object = $object;
    }
}
