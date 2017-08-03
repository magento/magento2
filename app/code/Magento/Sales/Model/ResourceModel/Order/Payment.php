<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;

/**
 * Flat sales order payment resource
 * @since 2.0.0
 */
class Payment extends SalesResource
{
    /**
     * Serializeable field: additional_information
     *
     * @var array
     * @since 2.0.0
     */
    protected $_serializableFields = ['additional_information' => [null, []]];

    /**
     * Event prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_payment_resource';

    /**
     * Model initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('sales_order_payment', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /**@var $object \Magento\Sales\Model\Order\Payment */
        parent::_beforeSave($object);
        if (!$object->getParentId() && $object->getOrder()) {
            $object->setParentId($object->getOrder()->getId());
        }
        return $this;
    }
}
