<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\QuoteRepository;

use Magento\Quote\Model\Quote\Address\BillingAddressPersister;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentPersister;
use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ResourceModel\Quote;

class SaveHandler
{
    /**
     * @var CartItemPersister
     */
    private $cartItemPersister;

    /**
     * @var BillingAddressPersister
     */
    private $billingAddressPersister;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResourceModel;

    /**
     * @var ShippingAssignmentPersister
     */
    private $shippingAssignmentPersister;

    /**
     * @param Quote $quoteResource
     * @param CartItemPersister $cartItemPersister
     * @param BillingAddressPersister $billingAddressPersister
     * @param ShippingAssignmentPersister $shippingAssignmentPersister
     */
    public function __construct(
        Quote $quoteResource,
        CartItemPersister $cartItemPersister,
        BillingAddressPersister $billingAddressPersister,
        ShippingAssignmentPersister $shippingAssignmentPersister
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
        if ($quote->getItems()) {
            foreach ($quote->getItems() as $item) {
                /** @var \Magento\Quote\Model\Quote\Item $item */
                if (!$item->isDeleted()) {
                    $quote->setLastAddedItem($this->cartItemPersister->save($quote, $item));
                }
            }
        }

        // Billing Address processing
        if ($quote->getBillingAddress() && $quote->getIsActive()) {
            $this->billingAddressPersister->save($quote, $quote->getBillingAddress());
        }

        // Shipping Assignments processing
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getShippingAssignments()) {
            $shippingAssignments = $quote->getExtensionAttributes()->getShippingAssignments();
            if (count($shippingAssignments) > 1) {
                throw new InputException(__("Only 1 shipping assignment can be set"));
            }
            $this->shippingAssignmentPersister->save($quote, $shippingAssignments[0]);
        }

        $this->quoteResourceModel->save($quote->collectTotals());
        return $quote;
    }
}
