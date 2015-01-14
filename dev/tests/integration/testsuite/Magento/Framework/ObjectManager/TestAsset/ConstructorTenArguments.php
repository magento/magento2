<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class ConstructorTenArguments extends \Magento\Framework\ObjectManager\TestAsset\ConstructorNineArguments
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_ten;

    /**
     * Ten arguments
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $two
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $three
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $four
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $five
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $six
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $seven
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $eight
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $nine
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $ten
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TestAsset\Basic $one,
        \Magento\Framework\ObjectManager\TestAsset\Basic $two,
        \Magento\Framework\ObjectManager\TestAsset\Basic $three,
        \Magento\Framework\ObjectManager\TestAsset\Basic $four,
        \Magento\Framework\ObjectManager\TestAsset\Basic $five,
        \Magento\Framework\ObjectManager\TestAsset\Basic $six,
        \Magento\Framework\ObjectManager\TestAsset\Basic $seven,
        \Magento\Framework\ObjectManager\TestAsset\Basic $eight,
        \Magento\Framework\ObjectManager\TestAsset\Basic $nine,
        \Magento\Framework\ObjectManager\TestAsset\Basic $ten
    ) {
        parent::__construct($one, $two, $three, $four, $five, $six, $seven, $eight, $nine);
        $this->_ten = $ten;
    }
}
