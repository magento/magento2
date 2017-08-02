<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Cart totals repository class for guest carts.
 * @since 2.0.0
 */
class GuestCartTotalRepository implements GuestCartTotalRepositoryInterface
{
    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartTotalRepositoryInterface
     * @since 2.0.0
     */
    private $cartTotalRepository;

    /**
     * Constructs a cart totals data object.
     *
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @since 2.0.0
     */
    public function __construct(
        CartTotalRepositoryInterface $cartTotalRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->cartTotalRepository->get($quoteIdMask->getQuoteId());
    }
}
