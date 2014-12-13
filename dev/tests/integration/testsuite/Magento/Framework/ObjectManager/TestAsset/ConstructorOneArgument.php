<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class ConstructorOneArgument
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_one;

    /**
     * One argument
     */

    /**
     * One argument
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     */
    public function __construct(\Magento\Framework\ObjectManager\TestAsset\Basic $one)
    {
        $this->_one = $one;
    }
}
