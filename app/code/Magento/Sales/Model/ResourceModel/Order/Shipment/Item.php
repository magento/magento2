<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Shipment;

use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Sales\Model\Spi\ShipmentItemResourceInterface;

/**
 * Flat sales order shipment item resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends SalesResource implements ShipmentItemResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_item_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_shipment_item', 'entity_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Item $object */
        if (!$object->getParentId() && $object->getShipment()) {
            $object->setParentId($object->getShipment()->getId());
        }

        return parent::_beforeSave($object);
    }
}
