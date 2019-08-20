<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;

/**
 * Create empty cart for customer
 */
class CreateEmptyCartForCustomer
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    private $quoteIdMaskResourceModel;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * Create empty cart for customer
     *
     * @param int $customerId
     * @param string|null $predefinedMaskedQuoteId
     * @return string
     */
    public function execute(int $customerId, string $predefinedMaskedQuoteId = null): string
    {
        $quoteId = (int) $this->cartManagement->createEmptyCartForCustomer($customerId);

        if ($predefinedMaskedQuoteId !== null) {
            $maskedId = $this->createPredefinedMaskId($quoteId, $predefinedMaskedQuoteId);
        } else {
            $maskedId = $this->getQuoteMaskId($quoteId);
        }

        return $maskedId;
    }

    /**
     * Create quote masked id from predefined value
     *
     * @param int $quoteId
     * @param string $maskId
     * @return string
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function createPredefinedMaskId(int $quoteId, string $maskId): string
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->setQuoteId($quoteId);
        $quoteIdMask->setMaskedId($maskId);

        $this->quoteIdMaskResourceModel->save($quoteIdMask);

        return $quoteIdMask->getMaskedId();
    }

    /**
     * Fetch or create masked id for customer's active quote
     *
     * @param int $quoteId
     * @return string
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuoteMaskId(int $quoteId): string
    {
        $maskedId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
        if ($maskedId === '') {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quoteId);

            $this->quoteIdMaskResourceModel->save($quoteIdMask);
            $maskedId = $quoteIdMask->getMaskedId();
        }

        return $maskedId;
    }
}
