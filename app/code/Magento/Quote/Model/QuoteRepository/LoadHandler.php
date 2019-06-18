<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\QuoteRepository;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor;
use Magento\Quote\Api\Data\CartExtensionFactory;

/**
 * Class LoadHandler
 *
 * Loads the quote
 *
 * @package Magento\Quote\Model\QuoteRepository
 */
class LoadHandler
{
    /**
     * @var ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @param ShippingAssignmentProcessor $shippingAssignmentProcessor
     * @param CartExtensionFactory $cartExtensionFactory
     */
    public function __construct(
        ShippingAssignmentProcessor $shippingAssignmentProcessor,
        CartExtensionFactory $cartExtensionFactory
    ) {
        $this->shippingAssignmentProcessor = $shippingAssignmentProcessor;
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * Adds items, extension attributes to quote
     *
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function load(CartInterface $quote)
    {
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
