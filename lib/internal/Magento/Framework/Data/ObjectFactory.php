<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Data;

/**
 * Class ObjectFactory
 * @package Magento\Framework\Data
 */
class ObjectFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create data object
     *
     * @param string $className
     * @param array $arguments
     * @return \Magento\Framework\DataObject
     */
    public function create($className, array $arguments)
    {
        return $this->objectManager->create($className, $arguments);
    }
}
