<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Factory class
 * @since 2.0.0
 */
class ValueFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\App\Config\ValueInterface::class
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
     * @since 2.0.0
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
