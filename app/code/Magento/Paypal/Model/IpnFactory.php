<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

class IpnFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $mapping
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $mapping = [])
    {
        $this->_objectManager = $objectManager;
        $this->mapping = $mapping;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Paypal\Model\IpnInterface
     */
    public function create(array $data = [])
    {
        $type = isset($data['data']['txn_type']) ? $data['data']['txn_type'] : '';
        $instanceType = isset($this->mapping[$type]) ? $this->mapping[$type] : 'Magento\Paypal\Model\Ipn';
        return $this->_objectManager->create($instanceType, $data);
    }
}
