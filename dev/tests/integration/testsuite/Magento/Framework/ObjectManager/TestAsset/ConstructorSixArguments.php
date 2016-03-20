<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class ConstructorSixArguments extends \Magento\Framework\ObjectManager\TestAsset\ConstructorFiveArguments
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_six;

    /**
     * Six arguments
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $two
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $three
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $four
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $five
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $six
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TestAsset\Basic $one,
        \Magento\Framework\ObjectManager\TestAsset\Basic $two,
        \Magento\Framework\ObjectManager\TestAsset\Basic $three,
        \Magento\Framework\ObjectManager\TestAsset\Basic $four,
        \Magento\Framework\ObjectManager\TestAsset\Basic $five,
        \Magento\Framework\ObjectManager\TestAsset\Basic $six
    ) {
        parent::__construct($one, $two, $three, $four, $five);
        $this->_six = $six;
    }
}
