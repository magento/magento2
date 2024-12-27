<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Factory class for \Magento\Framework\Validator
 *
 * @api
 */
class ValidatorFactory
{
    const DEFAULT_INSTANCE_NAME = Validator::class;

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
        $instanceName = self::DEFAULT_INSTANCE_NAME
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @param string $instanceName
     * @return \Magento\Framework\Validator
     */
    public function create(array $data = [], $instanceName = null)
    {
        if (null === $instanceName) {
            return $this->_objectManager->create($this->_instanceName, $data);
        } else {
            return $this->_objectManager->create($instanceName, $data);
        }
    }
}
