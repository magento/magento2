<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order;

use Magento\Framework\App\Resource;
use Magento\SalesSequence\Model\Manager;
use Magento\Sales\Model\Resource\Attribute;
use Magento\Sales\Model\Resource\EntityAbstract as SalesResource;
use Magento\Sales\Model\Resource\EntitySnapshot;
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
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param Attribute $attribute
     * @param Manager $sequenceManager
     * @param EntitySnapshot $entitySnapshot
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        Attribute $attribute,
        Manager $sequenceManager,
        EntitySnapshot $entitySnapshot,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $attribute, $sequenceManager, $entitySnapshot, $resourcePrefix);
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
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

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return $this
     */
    protected function processRelations(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $object */
        if (null !== $object->getItems()) {
            /**
             * Save invoice items
             */
            foreach ($object->getItems() as $item) {
                $item->setParentId($object->getId());
                $item->setOrderItem($item->getOrderItem());
                $item->save();
            }
        }

        if (null !== $object->getComments()) {
            foreach ($object->getComments() as $comment) {
                $comment->save();
            }
        }
        return parent::processRelations($object);
    }
}
