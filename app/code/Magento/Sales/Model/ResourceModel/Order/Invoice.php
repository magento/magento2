<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order;

use Magento\Framework\App\ResourceConnection;
use Magento\SalesSequence\Model\Manager;
use Magento\Sales\Model\ResourceModel\Attribute;
use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\Spi\InvoiceResourceInterface;

/**
 * Flat sales order invoice resource
 */
class Invoice extends SalesResource implements InvoiceResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_invoice', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $object */
        if (!$object->getOrderId() && $object->getOrder()) {
            $object->setOrderId($object->getOrder()->getId());
            $object->setBillingAddressId($object->getOrder()->getBillingAddress()->getId());
        }

        return parent::_beforeSave($object);
    }
}
