<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\QuoteRepository;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Api\Data\CartExtensionFactory;

/**
 * Class \Magento\Quote\Model\QuoteRepository\LoadHandler
 *
 * @since 2.1.0
 */
class LoadHandler
{
    /**
     * @var ShippingAssignmentProcessor
     * @since 2.1.0
     */
    private $shippingAssignmentProcessor;

    /**
     * @var CartExtensionFactory
     * @since 2.1.0
     */
    private $cartExtensionFactory;

    /**
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param CartExtensionFactory $cartExtensionFactory
     * @since 2.1.0
     */
    public function __construct(
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        CartExtensionFactory $cartExtensionFactory
    ) {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * @param CartInterface $quote
     * @return CartInterface
     * @since 2.1.0
     */
    public function load(CartInterface $quote)
    {
        if (!$quote->getIsActive()) {
            return $quote;
        }
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote->setItems($quote->getAllVisibleItems());
        $shippingAssignments = [];
        if (!$quote->isVirtual() && $quote->getItemsQty() > 0) {
            $shippingAssignments[] = $this->shippingAssignmentProcessor->create($quote);
        }
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        $cartExtension->setShippingAssignments($shippingAssignments);
        $quote->setExtensionAttributes($cartExtension);

        return $quote;
    }
}
