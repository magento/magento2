<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

class ShippingAddressAssignment
{
    /**
     * @var \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * ShippingAddressAssignment constructor.
     * @param \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory
     * @param Quote\ShippingAssignment\ShippingAssignmentProcessor $shippingAssignmentProcessor
     */
    public function __construct(
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory,
        \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor $shippingAssignmentProcessor
    ) {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @param bool $useForShipping
     * @return void
     */
    public function setAddress(
        \Magento\Quote\Api\Data\CartInterface $quote,
        \Magento\Quote\Api\Data\AddressInterface $address,
        $useForShipping = false
    ) {
        if ($useForShipping) {
            $quote->removeAddress($quote->getShippingAddress()->getId());
            $address->setSameAsBilling(1);
            $address->setCollectShippingRates(true);
        } else {
            $address = $quote->getShippingAddress()->setSameAsBilling(0);
        }

        $quote->setShippingAddress($address);
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->shippingAssignmentProcessor->create($quote);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        $quote->setExtensionAttributes($cartExtension);
    }
}
