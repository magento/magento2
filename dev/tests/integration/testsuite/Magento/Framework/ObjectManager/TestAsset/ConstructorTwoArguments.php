<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class ConstructorTwoArguments extends \Magento\Framework\ObjectManager\TestAsset\ConstructorOneArgument
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\Basic
     */
    protected $_two;

    /**
     * Two arguments
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $two
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TestAsset\Basic $one,
        \Magento\Framework\ObjectManager\TestAsset\Basic $two
    ) {
        parent::__construct($one);
        $this->_two = $two;
    }
}
