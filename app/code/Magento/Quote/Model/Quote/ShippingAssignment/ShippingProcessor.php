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

/**
 * Class \Magento\Quote\Model\Quote\ShippingAssignment\ShippingProcessor
 *
 * @since 2.1.0
 */
class ShippingProcessor
{
    /**
     * @var ShippingFactory
     * @since 2.1.0
     */
    private $shippingFactory;

    /**
     * @var ShippingAddressManagement
     * @since 2.1.0
     */
    private $shippingAddressManagement;

    /**
     * @var ShippingMethodManagement
     * @since 2.1.0
     */
    private $shippingMethodManagement;

    /**
     * @param ShippingFactory $shippingFactory
     * @param ShippingAddressManagement $shippingAddressManagement
     * @param ShippingMethodManagement $shippingMethodManagement
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function save(ShippingInterface $shipping, CartInterface $quote)
    {
        $this->shippingAddressManagement->assign($quote->getId(), $shipping->getAddress());
        if (!empty($shipping->getMethod()) && $quote->getItemsCount() > 0) {
            $nameComponents = explode('_', $shipping->getMethod());
            $carrierCode = array_shift($nameComponents);
            // carrier method code can contains more one name component
            $methodCode = implode('_', $nameComponents);
            $this->shippingMethodManagement->apply($quote->getId(), $carrierCode, $methodCode);
        }
    }
}
