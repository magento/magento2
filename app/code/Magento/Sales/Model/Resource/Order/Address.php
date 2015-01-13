<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order;

use Magento\Sales\Model\Resource\Entity as SalesResource;
use Magento\Sales\Model\Spi\OrderAddressResourceInterface;

/**
 * Flat sales order address resource
 */
class Address extends SalesResource implements OrderAddressResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_address_resource';

    /**
     * @var \Magento\Sales\Model\Order\Address\Validator
     */
    protected $_validator;

    /**
     * @var \Magento\Sales\Model\Resource\GridPool
     */
    protected $gridPool;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Sales\Model\Resource\Attribute $attribute
     * @param \Magento\Sales\Model\Increment $salesIncrement
     * @param \Magento\Sales\Model\Order\Address\Validator $validator
     * @param \Magento\Sales\Model\Resource\GridPool $gridPool
     * @param \Magento\Sales\Model\Resource\GridInterface $gridAggregator
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Sales\Model\Resource\Attribute $attribute,
        \Magento\Sales\Model\Increment $salesIncrement,
        \Magento\Sales\Model\Order\Address\Validator $validator,
        \Magento\Sales\Model\Resource\GridPool $gridPool,
        \Magento\Sales\Model\Resource\GridInterface $gridAggregator = null
    ) {
        $this->_validator = $validator;
        $this->gridPool = $gridPool;
        parent::__construct($resource, $attribute, $salesIncrement, $gridAggregator);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_address', 'entity_id');
    }

    /**
     * Return configuration for all attributes
     *
     * @return array
     */
    public function getAllAttributes()
    {
        $attributes = [
            'city' => __('City'),
            'company' => __('Company'),
            'country_id' => __('Country'),
            'email' => __('Email'),
            'firstname' => __('First Name'),
            'lastname' => __('Last Name'),
            'region_id' => __('State/Province'),
            'street' => __('Street Address'),
            'telephone' => __('Phone Number'),
            'postcode' => __('Zip/Postal Code'),
        ];
        asort($attributes);
        return $attributes;
    }

    /**
     * Performs validation before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeSave($object);
        if (!$object->getParentId() && $object->getOrder()) {
            $object->setParentId($object->getOrder()->getId());
        }
        // Init customer address id if customer address is assigned
        $customerData = $object->getCustomerAddressData();
        if ($customerData) {
            $object->setCustomerAddressId($customerData->getId());
        }
        $warnings = $this->_validator->validate($object);
        if (!empty($warnings)) {
            throw new \Magento\Framework\Model\Exception(
                __("Cannot save address") . ":\n" . implode("\n", $warnings)
            );
        }
        return $this;
    }

    /**
     * Update related grid table after object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $resource = parent::_afterSave($object);
        if ($object->hasDataChanges() && $object->getOrderId()) {
            $this->gridPool->refreshByOrderId($object->getOrderId());
        }
        return $resource;
    }
}
