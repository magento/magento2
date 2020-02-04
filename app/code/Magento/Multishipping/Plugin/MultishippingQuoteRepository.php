<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Plugin;

use Magento\Framework\Api\SearchResultsInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\ShippingAssignment\ShippingProcessor;
use Magento\Quote\Model\ShippingAssignmentFactory;

/**
 * Plugin for multishipping quote processing.
 */
class MultishippingQuoteRepository
{
    /**
     * @var ShippingAssignmentFactory
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingProcessor
     */
    private $shippingProcessor;

    /**
     * @param ShippingAssignmentFactory $shippingAssignmentFactory
     * @param ShippingProcessor $shippingProcessor
     */
    public function __construct(
        ShippingAssignmentFactory $shippingAssignmentFactory,
        ShippingProcessor $shippingProcessor
    ) {
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shippingProcessor = $shippingProcessor;
    }

    /**
     * Process multishipping quote for get.
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        CartRepositoryInterface $subject,
        CartInterface $result
    ) {
        return $this->processQuote($result);
    }

    /**
     * Process multishipping quote for get list.
     *
     * @param CartRepositoryInterface $subject
     * @param SearchResultsInterface $result
     *
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        CartRepositoryInterface $subject,
        SearchResultsInterface $result
    ) {
        $items = [];
        foreach ($result->getItems() as $item) {
            $items[] = $this->processQuote($item);
        }
        $result->setItems($items);

        return $result;
    }

    /**
     * Remove shipping assignments for multishipping quote.
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote)
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if ($quote->getIsMultiShipping() && $extensionAttributes && $extensionAttributes->getShippingAssignments()) {
            $quote->getExtensionAttributes()->setShippingAssignments([]);
        }

        return [$quote];
    }

    /**
     * Set shipping assignments for multishipping quote according to customer selection.
     *
     * @param CartInterface $quote
     * @return CartInterface
     */
    private function processQuote(CartInterface $quote): CartInterface
    {
        if (!$quote->getIsMultiShipping() || !$quote instanceof Quote) {
            return $quote;
        }

        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getShippingAssignments()) {
            $shippingAssignments = [];
            $addresses = $quote->getAllAddresses();

            foreach ($addresses as $address) {
                $quoteItems = $this->getQuoteItems($quote, $address);
                if (!empty($quoteItems)) {
                    $shippingAssignment = $this->shippingAssignmentFactory->create();
                    $shippingAssignment->setItems($quoteItems);
                    $shippingAssignment->setShipping($this->shippingProcessor->create($address));
                    $shippingAssignments[] = $shippingAssignment;
                }
            }

            if (!empty($shippingAssignments)) {
                $quote->getExtensionAttributes()->setShippingAssignments($shippingAssignments);
            }
        }

        return $quote;
    }

    /**
     * Returns quote items assigned to address.
     *
     * @param Quote $quote
     * @param Quote\Address $address
     * @return Quote\Item[]
     */
    private function getQuoteItems(Quote $quote, Quote\Address $address): array
    {
        $quoteItems = [];
        foreach ($address->getItemsCollection() as $addressItem) {
            $quoteItem = $quote->getItemById($addressItem->getQuoteItemId());
            if ($quoteItem) {
                $multishippingQuoteItem = clone $quoteItem;
                $qty = $addressItem->getQty();
                $sku = $multishippingQuoteItem->getSku();
                if (isset($quoteItems[$sku])) {
                    $qty += $quoteItems[$sku]->getQty();
                }
                $multishippingQuoteItem->setQty($qty);
                $quoteItems[$sku] = $multishippingQuoteItem;
            }
        }

        return array_values($quoteItems);
    }
}
