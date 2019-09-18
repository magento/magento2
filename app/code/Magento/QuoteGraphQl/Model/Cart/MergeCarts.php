<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Merge two carts
 */
class MergeCarts
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    private $quoteMaskResource;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param QuoteIdMaskFactory $quoteMaskFactory
     * @param QuoteIdMaskResourceModel $quoteMaskResource
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        QuoteIdMaskFactory $quoteMaskFactory,
        QuoteIdMaskResourceModel $quoteMaskResource,
        CartRepositoryInterface $cartRepository
    ) {
        $this->quoteMaskFactory = $quoteMaskFactory;
        $this->quoteMaskResource = $quoteMaskResource;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Merge two quotes
     *
     * @param Quote $firstCart
     * @param Quote $secondQuote
     * @return string
     */
    public function execute(Quote $firstCart, Quote $secondQuote): string
    {
        $firstCart->merge($secondQuote);
        $firstCart->setIsActive(true);

        $this->updateMaskedId($secondQuote);
        $maskedQuoteId = $this->updateMaskedId($firstCart);

        $this->cartRepository->save($firstCart);

        $secondQuote->setIsActive(false);
        $this->cartRepository->save($secondQuote);

        return $maskedQuoteId;
    }

    /**
     * Update quote masked id
     *
     * @param Quote $quote
     * @return string
     */
    private function updateMaskedId(Quote $quote): string
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteMaskFactory->create();
        $this->quoteMaskResource->load($quoteIdMask, $quote->getId(), 'quote_id');
        $quoteIdMask->unsetData('masked_id');
        $this->quoteMaskResource->save($quoteIdMask);
        $maskedId = $quoteIdMask->getMaskedId();

        return $maskedId;
    }
}
