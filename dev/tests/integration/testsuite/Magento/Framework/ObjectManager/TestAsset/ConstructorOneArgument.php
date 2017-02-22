<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
