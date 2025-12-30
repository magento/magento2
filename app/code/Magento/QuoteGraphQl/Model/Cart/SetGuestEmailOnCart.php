<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Set guest email for a specified shopping cart
 */
class SetGuestEmailOnCart
{
    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        protected CartRepositoryInterface $cartRepository
    ) {
    }

    /**
     * Set guest email for a specified shopping cart
     *
     * @param ContextInterface $context
     * @param CartInterface $cart
     * @param string $email
     * @return void
     * @throws LocalizedException
     */
    public function execute(ContextInterface $context, CartInterface $cart, string $email): void
    {
        $cart->setCustomerEmail($email);

        try {
            $this->cartRepository->save($cart);
        }  catch (CouldNotSaveException $e) {
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
