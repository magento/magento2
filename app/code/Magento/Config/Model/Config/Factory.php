<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

/**
 * System configuration object factory
 */
namespace Magento\Config\Model\Config;

/**
 * @api
 * @since 100.0.2
 */
class Factory
{
    /**
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
     * Create new config object
     *
     * @param array $data
     * @return \Magento\Config\Model\Config
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create(\Magento\Config\Model\Config::class, $data);
    }
}
