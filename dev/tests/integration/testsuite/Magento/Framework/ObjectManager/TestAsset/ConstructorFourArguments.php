<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class ConstructorFourArguments extends \Magento\Framework\ObjectManager\TestAsset\ConstructorThreeArguments
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_four;

    /**
     * Four arguments
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $two
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $three
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $four
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TestAsset\Basic $one,
        \Magento\Framework\ObjectManager\TestAsset\Basic $two,
        \Magento\Framework\ObjectManager\TestAsset\Basic $three,
        \Magento\Framework\ObjectManager\TestAsset\Basic $four
    ) {
        parent::__construct($one, $two, $three);
        $this->_four = $four;
    }
}
