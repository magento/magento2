<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order;

use Magento\Framework\App\Resource as AppResource;
use Magento\Sales\Model\Increment as SalesIncrement;
use Magento\Sales\Model\Resource\Attribute;
use Magento\Sales\Model\Resource\Entity as SalesResource;
use Magento\Sales\Model\Resource\Order\Shipment\Grid as ShipmentGrid;
use Magento\Sales\Model\Spi\ShipmentResourceInterface;

/**
 * Flat sales order shipment resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipment extends SalesResource implements ShipmentResourceInterface
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_order_shipment_resource';

    /**
     * Fields that should be serialized before persistence
     *
     * @var array
     */
    protected $_serializableFields = ['packages' => [[], []]];

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_shipment', 'entity_id');
    }

    /**
     * @param AppResource $resource
     * @param Attribute $attribute
     * @param SalesIncrement $salesIncrement
     * @param ShipmentGrid $gridAggregator
     */
    public function __construct(
        AppResource $resource,
        Attribute $attribute,
        SalesIncrement $salesIncrement,
        ShipmentGrid $gridAggregator
    ) {
        parent::__construct($resource, $attribute, $salesIncrement, $gridAggregator);
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $object */
        if ((!$object->getId() || null !== $object->getItems()) && !count($object->getAllItems())) {
            throw new \Magento\Framework\Model\Exception(__('We cannot create an empty shipment.'));
        }

        if (!$object->getOrderId() && $object->getOrder()) {
            $object->setOrderId($object->getOrder()->getId());
            $object->setShippingAddressId($object->getOrder()->getShippingAddress()->getId());
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
        /** @var \Magento\Sales\Model\Order\Shipment $object */
        if (null !== $object->getItems()) {
            foreach ($object->getItems() as $item) {
                $item->setParentId($object->getId());
                $item->save();
            }
        }

        if (null !== $object->getTracks()) {
            foreach ($object->getTracks() as $track) {
                $track->save();
            }
        }

        if (null !== $object->getComments()) {
            foreach ($object->getComments() as $comment) {
                $comment->save();
            }
        }

        return parent::_afterSave($object);
    }
}
