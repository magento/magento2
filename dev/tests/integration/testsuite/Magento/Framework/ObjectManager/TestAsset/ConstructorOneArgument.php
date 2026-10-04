<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
     *
     * @param \Magento\Framework\ObjectManager\TestAsset\Basic $one
     */
    public function __construct(\Magento\Framework\ObjectManager\TestAsset\Basic $one)
    {
        $this->_one = $one;
    }

    /**
     * @return Basic
     */
    public function getBasicDependency()
    {
        return $this->_one;
    }
}
