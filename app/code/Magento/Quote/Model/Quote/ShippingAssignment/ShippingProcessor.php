<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\ShippingAssignment;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\ShippingFactory;
use Magento\Quote\Model\ShippingAddressManagement;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Sales\Model\Order;

class ShippingProcessor
{
    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var ShippingAddressManagement
     */
    private $shippingAddressManagement;

    /**
     * @var ShippingMethodManagement
     */
    private $shippingMethodManagement;

    /**
     * @param ShippingFactory $shippingFactory
     * @param ShippingAddressManagement $shippingAddressManagement
     * @param ShippingMethodManagement $shippingMethodManagement
     */
    public function __construct(
        ShippingFactory $shippingFactory,
        ShippingAddressManagement $shippingAddressManagement,
        ShippingMethodManagement $shippingMethodManagement
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $shippingAddress
     * @return \Magento\Quote\Api\Data\ShippingInterface
     */
    public function create(\Magento\Quote\Api\Data\AddressInterface $shippingAddress)
    {
        /** @var \Magento\Quote\Api\Data\ShippingInterface $shipping */
        $shipping = $this->shippingFactory->create();
        $shipping->setMethod($shippingAddress->getShippingMethod());
        $shipping->setAddress($shippingAddress);
        return $shipping;
    }

    /**
     * @param ShippingInterface $shipping
     * @param CartInterface $quote
     * @return void
     */
    public function save(ShippingInterface $shipping, CartInterface $quote)
    {
        $this->shippingAddressManagement->assign($quote->getId(), $shipping->getAddress());
        if (!empty($shipping->getMethod()) && $quote->getItemsCount() > 0) {
            $nameComponents = explode(Order::DELIMITER_SHIPPING_METHOD, $shipping->getMethod());
            $carrierCode = $nameComponents[0];
            $methodCode = $nameComponents[1];
            $this->shippingMethodManagement->apply($quote->getId(), $carrierCode, $methodCode);
        }
    }
}
