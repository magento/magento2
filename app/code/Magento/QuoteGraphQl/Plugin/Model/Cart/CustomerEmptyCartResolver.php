<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\QuoteGraphQl\Plugin\Model\Cart;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\CreateEmptyCartForCustomer;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Closure;

/**
 * Get customer cart or create empty cart. Ensure mask_id is created
 */
class CustomerEmptyCartResolver
{
    /**
     * @param CartManagementInterface $cartManagement
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     */
    public function __construct(
        private readonly CartManagementInterface $cartManagement,
        private readonly CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        private readonly QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory,
        private readonly QuoteIdMaskResourceModel $quoteIdMaskResourceModel
    ) {
    }

    /**
     * Get customer cart by customer id with predefined masked quote id
     *
     * @param CustomerCartResolver $subject
     * @param Closure $proceed
     * @param int $customerId
     * @param null $predefinedMaskedQuoteId
     * @return Quote
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundResolve(
        CustomerCartResolver $subject,
        Closure $proceed,
        int $customerId,
        $predefinedMaskedQuoteId = null
    ): Quote {
        try {
            /** @var Quote $cart */
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($customerId);
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        }
        try {
            $this->ensureQuoteMaskIdExist((int)$cart->getId(), $predefinedMaskedQuoteId);
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        } catch (AlreadyExistsException $e) {
            // do nothing, we already have masked id
        }

        return $cart;
    }

    /**
     * Create masked id for customer's active quote if it's not exists
     *
     * @param int $quoteId
     * @param null $predefinedMaskedQuoteId
     * @return void
     * @throws AlreadyExistsException
     */
    private function ensureQuoteMaskIdExist(int $quoteId, $predefinedMaskedQuoteId): void
    {
        try {
            $maskedId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
        } catch (NoSuchEntityException $e) {
            $maskedId = '';
        }
        if ($maskedId === '') {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quoteId);
            if (null !== $predefinedMaskedQuoteId) {
                $quoteIdMask->setMaskedId($predefinedMaskedQuoteId);
            }
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
        }
    }
}
