<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Config\Model\Config;

/**
 * @api
 * @since 100.0.2
 */
class SourceFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create backend model by name
     *
     * @param string $modelName
     * @return \Magento\Framework\Option\ArrayInterface
     */
    public function create($modelName)
    {
        $model = $this->_objectManager->get($modelName);
        return $model;
    }
}
