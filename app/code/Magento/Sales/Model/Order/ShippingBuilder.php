<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\ShippingInterface;
use Magento\Sales\Api\Data\ShippingInterfaceFactory;
use Magento\Sales\Api\Data\TotalInterface;
use Magento\Sales\Api\Data\TotalInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;

/**
 * Class ShippingBuilder
 * @package Magento\Sales\Model\Order
 */
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
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * ShippingBuilder constructor.
     *
     * @param OrderFactory $orderFactory
     * @param ShippingInterfaceFactory $shippingFactory
     * @param TotalInterfaceFactory $totalFactory
     * @param AddressCollectionFactory $addressCollectionFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        ShippingInterfaceFactory $shippingFactory,
        TotalInterfaceFactory $totalFactory,
        AddressCollectionFactory $addressCollectionFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->shippingFactory = $shippingFactory;
        $this->totalFactory = $totalFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return ShippingInterface|null
     */
    public function create()
    {
        $shipping = null;
        if ($this->getOrderId()) {
            $this->order = $this->orderFactory->create()->load($this->getOrderId());
            if ($this->order->getEntityId()) {
                /** @var ShippingInterface $shipping */
                $shipping = $this->shippingFactory->create();
                $shipping->setAddress($this->getAddress());
                $shipping->setMethod($this->order->getShippingMethod());
                $shipping->setTotal($this->getTotal());
            }
        }
        return $shipping;
    }

    /**
     * @return int|null
     */
    private function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return OrderAddressInterface|null
     */
    private function getAddress()
    {
        $collection = $this->addressCollectionFactory->create()->setOrderFilter($this->order);
        /** @var OrderAddressInterface $address  */
        foreach ($collection as $address) {
            if ($address->getAddressType() == Address::TYPE_SHIPPING) { // && !$address->isDeleted()) {
                return $address;
            }
        }
        return null;
    }

    /**
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
