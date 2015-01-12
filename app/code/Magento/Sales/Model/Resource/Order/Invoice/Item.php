<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Invoice;

/**
 * Flat sales order invoice item resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends \Magento\Sales\Model\Resource\Entity
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice_item_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_invoice_item', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Invoice\Item $object */
        if (!$object->getParentId() && $object->getInvoice()) {
            $object->setParentId($object->getInvoice()->getId());
        }

        return parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Invoice\Item $object */
        if (null == !$object->getOrderItem()) {
            $object->getOrderItem()->save();
        }
        return parent::_afterSave($object);
    }
}
