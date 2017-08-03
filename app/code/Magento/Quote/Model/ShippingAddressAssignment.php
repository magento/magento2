<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

/**
 * Class \Magento\Quote\Model\ShippingAddressAssignment
 *
 * @since 2.2.0
 */
class ShippingAddressAssignment
{
    /**
     * @var \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor
     * @since 2.2.0
     */
    private $shippingAssignmentProcessor;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     * @since 2.2.0
     */
    private $cartExtensionFactory;

    /**
     * ShippingAddressAssignment constructor.
     * @param \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory
     * @param Quote\ShippingAssignment\ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @since 2.2.0
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
     * @since 2.2.0
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
