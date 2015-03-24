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
     * Events manager.
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

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
     * @param \Magento\Framework\Model\Resource\Db\Context $context
     * @param Attribute $attribute
     * @param SalesIncrement $salesIncrement
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Db\Context $context,
        Attribute $attribute,
        SalesIncrement $salesIncrement,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $resourcePrefix = null
    ) {
        $this->eventManager = $eventManager;
        parent::__construct($context, $attribute, $salesIncrement, $resourcePrefix);
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\Object $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Shipment $object */
        if ((!$object->getId() || null !== $object->getItems()) && !count($object->getAllItems())) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We cannot create an empty shipment.'));
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

        $this->eventManager->dispatch(
            $this->_eventPrefix . '_save_after', ['entity' => $object]
        );

        return parent::_afterSave($object);
    }

    /**
     * Dispatches corresponding event after the deletion of the order shipment.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->eventManager->dispatch(
            $this->_eventPrefix . '_delete_after', ['entity' => $object]
        );

        return parent::_afterDelete($object);
    }
}
