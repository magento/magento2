<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterfaceFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\Data\OrderInterface;

class ShippingAssignmentBuilder
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var ShippingAssignmentInterfaceFactory
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingBuilderFactory
     */
    private $shippingBuilderFactory;

    /**
     * @var int|null
     */
    private $orderId = null;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * ShippingAssignment constructor.
     *
     * @param OrderFactory $orderFactory
     * @param ShippingAssignmentInterfaceFactory $shippingAssignmentFactory
     * @param ShippingBuilderFactory $shippingBuilderFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        ShippingAssignmentInterfaceFactory $shippingAssignmentFactory,
        ShippingBuilderFactory $shippingBuilderFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shippingBuilderFactory = $shippingBuilderFactory;
    }

    /**
     * Setter for orderId property
     *
     * @param int $orderId
     * @return void
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Setter for order property
     *
     * @param OrderInterface $order
     * @return void
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
        $this->orderId = $order->getEntityId();
    }

    /**
     * Getter for orderId property
     *
     * @return int|null
     */
    private function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get order
     *
     * @return OrderInterface
     */
    private function getOrder() : OrderInterface
    {
        if ($this->order === null) {
            $this->order = $this->orderFactory->create()->load($this->getOrderId());
        }
        return $this->order;
    }

    /**
     * Create shipment assignement
     *
     * @return ShippingAssignmentInterface[]|null
     */
    public function create()
    {
        $shippingAssignments = null;
        if ($this->getOrderId()) {
            /** @var ShippingAssignmentInterface $shippingAssignment */
            $shippingAssignment =  $this->shippingAssignmentFactory->create();

            $shipping = $this->shippingBuilderFactory->create();
            $shipping->setOrder($this->getOrder());
            $shippingAssignment->setShipping($shipping->create());
            $shippingAssignment->setItems($this->getOrder()->getItems());
            $shippingAssignment->setStockId($this->getOrder()->getStockId());
            //for now order has only one shipping assignment
            $shippingAssignments = [$shippingAssignment];
        }
        return $shippingAssignments;
    }
}
