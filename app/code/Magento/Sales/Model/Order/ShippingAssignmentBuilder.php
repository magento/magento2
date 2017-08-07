<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterfaceFactory;
use Magento\Sales\Model\OrderFactory;

/**
 * Class ShippingAssignmentBuilder
 * @package Magento\Sales\Model\Order
 * @since 2.0.3
 */
class ShippingAssignmentBuilder
{
    /**
     * @var OrderFactory
     * @since 2.0.3
     */
    private $orderFactory;

    /**
     * @var ShippingAssignmentInterfaceFactory
     * @since 2.0.3
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingBuilderFactory
     * @since 2.0.3
     */
    private $shippingBuilderFactory;

    /**
     * @var int|null
     * @since 2.0.3
     */
    private $orderId = null;

    /**
     * ShippingAssignment constructor.
     *
     * @param OrderFactory $orderFactory
     * @param ShippingAssignmentInterfaceFactory $shippingAssignmentFactory
     * @param ShippingBuilderFactory $shippingBuilderFactory
     * @since 2.0.3
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
     * @since 2.0.3
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return int|null
     * @since 2.0.3
     */
    private function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return ShippingAssignmentInterface[]|null
     * @since 2.0.3
     */
    public function create()
    {
        $shippingAssignments = null;
        if ($this->getOrderId()) {
            $order = $this->orderFactory->create()->load($this->getOrderId());
            /** @var ShippingAssignmentInterface $shippingAssignment */
            $shippingAssignment =  $this->shippingAssignmentFactory->create();

            $shipping = $this->shippingBuilderFactory->create();
            $shipping->setOrderId($this->getOrderId());
            $shippingAssignment->setShipping($shipping->create());
            $shippingAssignment->setItems($order->getItems());
            $shippingAssignment->setStockId($order->getStockId());
            //for now order has only one shipping assignment
            $shippingAssignments = [$shippingAssignment];
        }
        return $shippingAssignments;
    }
}
