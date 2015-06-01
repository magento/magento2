<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\JoinProcessor;

/**
 * Factory class for @see
 * \Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinData
 */
class ExtensionAttributeJoinDataFactory
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
        $instanceName = '\\Magento\\Framework\\Api\\JoinProcessor\\ExtensionAttributeJoinData'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Api\JoinProcessor\ExtensionAttributeJoinData
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
