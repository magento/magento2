<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\QuoteRepository;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\InputException;

class SaveHandler
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\CartItemPersister
     */
    private $cartItemPersister;

    /**
     * @var \Magento\Quote\Model\Quote\Address\BillingAddressPersister
     */
    private $billingAddressPersister;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResourceModel;

    /**
     * @var \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister
     */
    private $shippingAssignmentPersister;

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param \Magento\Quote\Model\Quote\Item\CartItemPersister $cartItemPersister
     * @param \Magento\Quote\Model\Quote\Address\BillingAddressPersister $billingAddressPersister
     * @param \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister $shippingAssignmentPersister
     */
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource,
        \Magento\Quote\Model\Quote\Item\CartItemPersister $cartItemPersister,
        \Magento\Quote\Model\Quote\Address\BillingAddressPersister $billingAddressPersister,
        \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister $shippingAssignmentPersister
    ) {
        $this->quoteResourceModel = $quoteResource;
        $this->cartItemPersister = $cartItemPersister;
        $this->billingAddressPersister = $billingAddressPersister;
        $this->shippingAssignmentPersister = $shippingAssignmentPersister;
    }

    /**
     * @param CartInterface $quote
     * @return CartInterface
     *
     * @throws InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(CartInterface $quote)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        // Quote Item processing
        $items = $quote->getItems();
        if ($items) {
            foreach ($items as $item) {
                /** @var \Magento\Quote\Model\Quote\Item $item */
                if (!$item->isDeleted()) {
                    $quote->setLastAddedItem($this->cartItemPersister->save($quote, $item));
                }
            }
        }

        // Billing Address processing
        $billingAddress = $quote->getBillingAddress();
        if ($billingAddress) {
            $this->billingAddressPersister->save($quote, $billingAddress);
        }

        $this->processShippingAssignment($quote);

        $this->quoteResourceModel->save($quote->collectTotals());
        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return void
     * @throws InputException
     */
    private function processShippingAssignment($quote)
    {
        // Shipping Assignments processing
        $extensionAttributes = $quote->getExtensionAttributes();
        if (!$quote->isVirtual() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
            $shippingAssignments = $extensionAttributes->getShippingAssignments();
            if (count($shippingAssignments) > 1) {
                throw new InputException(__("Only 1 shipping assignment can be set"));
            }
            $this->shippingAssignmentPersister->save($quote, $shippingAssignments[0]);
        }
    }
}
