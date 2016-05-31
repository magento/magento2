<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Factory class
 */
class ValueFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'Magento\Framework\App\Config\ValueInterface'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\App\Config\ValueInterface
     * @throws \InvalidArgumentException
     */
    public function create(array $data = [])
    {
        $model = $this->_objectManager->create($this->_instanceName, $data);
        if (!$model instanceof \Magento\Framework\App\Config\ValueInterface) {
            throw new \InvalidArgumentException('Invalid config field model: ' . $this->_instanceName);
        }
        return $model;
    }
}
