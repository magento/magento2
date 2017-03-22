<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class ConstructorSevenArguments extends \Magento\Framework\ObjectManager\TestAsset\ConstructorSixArguments
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_seven;

    /**
     * Seven arguments
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $two
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $three
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $four
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $five
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $six
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $seven
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TestAsset\Basic $one,
        \Magento\Framework\ObjectManager\TestAsset\Basic $two,
        \Magento\Framework\ObjectManager\TestAsset\Basic $three,
        \Magento\Framework\ObjectManager\TestAsset\Basic $four,
        \Magento\Framework\ObjectManager\TestAsset\Basic $five,
        \Magento\Framework\ObjectManager\TestAsset\Basic $six,
        \Magento\Framework\ObjectManager\TestAsset\Basic $seven
    ) {
        parent::__construct($one, $two, $three, $four, $five, $six);
        $this->_seven = $seven;
    }
}
