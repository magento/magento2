<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\ShippingInterface;
use Magento\Sales\Api\Data\ShippingInterfaceFactory;
use Magento\Sales\Api\Data\TotalInterface;
use Magento\Sales\Api\Data\TotalInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;

class ShippingBuilder
{
    /**
     * @var int|null
     */
    private $orderId = null;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var ShippingInterfaceFactory
     */
    private $shippingFactory;

    /**
     * @var TotalInterfaceFactory
     */
    private $totalFactory;

    /**
     * ShippingBuilder constructor.
     *
     * @param OrderFactory $orderFactory
     * @param ShippingInterfaceFactory $shippingFactory
     * @param TotalInterfaceFactory $totalFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        ShippingInterfaceFactory $shippingFactory,
        TotalInterfaceFactory $totalFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->shippingFactory = $shippingFactory;
        $this->totalFactory = $totalFactory;
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
     * Create shipping
     *
     * @return ShippingInterface|null
     */
    public function create()
    {
        $shipping = null;
        if ($this->getOrderId()) {
            if ($this->getOrder()->getEntityId()) {
                /** @var ShippingInterface $shipping */
                $shipping = $this->shippingFactory->create();
                $shippingAddress = $this->order->getShippingAddress();
                if ($shippingAddress) {
                    $shipping->setAddress($shippingAddress);
                }
                $shipping->setMethod($this->order->getShippingMethod());
                $shipping->setTotal($this->getTotal());
            }
        }
        return $shipping;
    }

    /**
     * Getter for the $orderId property
     *
     * @return int|null
     */
    private function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get order by id
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
     * Create total
     *
     * @return TotalInterface
     */
    private function getTotal()
    {
        /** @var TotalInterface $total */
        $total = $this->totalFactory->create();
        $total->setBaseShippingAmount($this->order->getBaseShippingAmount());
        $total->setBaseShippingCanceled($this->order->getBaseShippingCanceled());
        $total->setBaseShippingDiscountAmount($this->order->getBaseShippingDiscountAmount());
        $total->setBaseShippingDiscountTaxCompensationAmnt($this->order->getBaseShippingDiscountTaxCompensationAmnt());
        $total->setBaseShippingInclTax($this->order->getBaseShippingInclTax());
        $total->setBaseShippingInvoiced($this->order->getBaseShippingInvoiced());
        $total->setBaseShippingRefunded($this->order->getBaseShippingRefunded());
        $total->setBaseShippingTaxAmount($this->order->getBaseShippingTaxAmount());
        $total->setBaseShippingTaxRefunded($this->order->getBaseShippingTaxRefunded());
        $total->setShippingAmount($this->order->getShippingAmount());
        $total->setShippingCanceled($this->order->getShippingCanceled());
        $total->setShippingDiscountAmount($this->order->getShippingDiscountAmount());
        $total->setShippingDiscountTaxCompensationAmount($this->order->getShippingDiscountTaxCompensationAmount());
        $total->setShippingInclTax($this->order->getShippingInclTax());
        $total->setShippingInvoiced($this->order->getShippingInvoiced());
        $total->setShippingRefunded($this->order->getShippingRefunded());
        $total->setShippingTaxAmount($this->order->getShippingTaxAmount());
        $total->setShippingTaxRefunded($this->order->getShippingTaxRefunded());
        return $total;
    }
}
