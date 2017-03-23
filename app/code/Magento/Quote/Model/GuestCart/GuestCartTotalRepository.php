<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\GuestCartTotalRepositoryInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Cart totals repository class for guest carts.
 */
class GuestCartTotalRepository implements GuestCartTotalRepositoryInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    /**
     * Constructs a cart totals data object.
     *
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
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
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->cartTotalRepository->get($quoteIdMask->getQuoteId());
    }
}
