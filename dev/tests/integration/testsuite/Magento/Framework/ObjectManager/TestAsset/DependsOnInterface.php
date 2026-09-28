<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class DependsOnInterface
{
    /**
     * @var \Magento\Framework\ObjectManager\TestAsset\TestAssetInterface
     */
    protected $_object;

    /**
     * @param \Magento\Framework\ObjectManager\TestAsset\TestAssetInterface $object
     */
    public function __construct(\Magento\Framework\ObjectManager\TestAsset\TestAssetInterface $object)
    {
        $this->_object = $object;
    }

    /**
     * @return TestAssetInterface
     */
    public function getInterfaceDependency()
    {
        return $this->_object;
    }
}
