<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

/**
 * Class \Magento\Paypal\Model\IpnFactory
 *
 * @since 2.0.0
 */
class IpnFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $mapping = [];

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $mapping
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        $type = isset($data['data']['txn_type']) ? $data['data']['txn_type'] : '';
        $instanceType = isset($this->mapping[$type]) ? $this->mapping[$type] : \Magento\Paypal\Model\Ipn::class;
        return $this->_objectManager->create($instanceType, $data);
    }
}
