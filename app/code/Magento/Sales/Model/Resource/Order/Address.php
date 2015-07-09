<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order;

use Magento\Sales\Model\Resource\EntityAbstract as SalesResource;
use Magento\Sales\Model\Spi\OrderAddressResourceInterface;
use Magento\Framework\Model\Resource\Db\VersionControl\Snapshot;

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
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param \Magento\Sales\Model\Resource\Attribute $attribute
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\Resource\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Sales\Model\Order\Address\Validator $validator
     * @param \Magento\Sales\Model\Resource\GridPool $gridPool
     * @param string $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\Resource\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Sales\Model\Resource\Attribute $attribute,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Magento\Sales\Model\Order\Address\Validator $validator,
        \Magento\Sales\Model\Resource\GridPool $gridPool,
        $resourcePrefix = null
    ) {
        $this->_validator = $validator;
        $this->gridPool = $gridPool;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $resourcePrefix
        );
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
            throw new \Magento\Framework\Exception\LocalizedException(
                __("We can't save the address:\n%1", implode("\n", $warnings))
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
        if ($object->getParentId()) {
            $this->gridPool->refreshByOrderId($object->getParentId());
        }
        return $resource;
    }
}
