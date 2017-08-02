<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Sales\Model\Spi\OrderAddressResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Flat sales order address resource
 * @since 2.0.0
 */
class Address extends SalesResource implements OrderAddressResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_address_resource';

    /**
     * @var \Magento\Sales\Model\Order\Address\Validator
     * @since 2.0.0
     */
    protected $_validator;

    /**
     * @var \Magento\Sales\Model\ResourceModel\GridPool
     * @since 2.0.0
     */
    protected $gridPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Attribute $attribute
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Sales\Model\Order\Address\Validator $validator
     * @param \Magento\Sales\Model\ResourceModel\GridPool $gridPool
     * @param string $connectionName
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Sales\Model\ResourceModel\Attribute $attribute,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Magento\Sales\Model\Order\Address\Validator $validator,
        \Magento\Sales\Model\ResourceModel\GridPool $gridPool,
        $connectionName = null
    ) {
        $this->_validator = $validator;
        $this->gridPool = $gridPool;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $connectionName
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('sales_order_address', 'entity_id');
    }

    /**
     * Return configuration for all attributes
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
}
