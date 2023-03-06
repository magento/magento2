<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterfaceFactory;
use Magento\Sales\Model\OrderFactory;

/**
 * Class ShippingAssignmentBuilder
 * @package Magento\Sales\Model\Order
 */
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
     * @param int $orderId
     * @return void
     */
    public function setOrderId($orderId)
    {
        $this->order = $this->orderFactory->create()->load($orderId);
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @return OrderInterface
     */
    private function getOrder()
    {
        return $this->order;
    }

    /**
     * @return ShippingAssignmentInterface[]|null
     */
    public function create()
    {
        $shippingAssignments = null;
        if ($order = $this->getOrder()) {
            /** @var ShippingAssignmentInterface $shippingAssignment */
            $shippingAssignment =  $this->shippingAssignmentFactory->create();

            $shipping = $this->shippingBuilderFactory->create();
            $shipping->setOrder($order);
            $shippingAssignment->setShipping($shipping->create());
            $shippingAssignment->setItems($order->getItems());
            $shippingAssignment->setStockId($order->getStockId());
            //for now order has only one shipping assignment
            $shippingAssignments = [$shippingAssignment];
        }
        return $shippingAssignments;
    }
}
