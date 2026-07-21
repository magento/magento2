<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\GuestCart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Get cart for guest users.
 */
class GetGuestCart
{
    /**
     * Initialize dependencies
     *
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository
    ) {
    }

    /**
     * Get quote by masked cart ID only if it is a guest cart.
     *
     * @param string $maskedCartId
     * @param int $quoteId
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function execute(string $maskedCartId, int $quoteId): Quote
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($quoteId);
        $this->checkIsGuestCart((int) $quote->getCustomerId(), $maskedCartId);

        return $quote;
    }

    /**
     * Check if the cart is a guest cart.
     *
     * @param int $customerId
     * @param string $maskedCartId
     * @throws NoSuchEntityException
     */
    public function checkIsGuestCart(int $customerId, string $maskedCartId): void
    {
        if ($customerId !== 0) {
            throw new NoSuchEntityException(
                __("Could not find a cart with ID '%masked_cart_id'", ['masked_cart_id' => $maskedCartId])
            );
        }
    }
}
