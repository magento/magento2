<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

    public function getBasicDependency()
    {
        return $this->_object;
    }
}
