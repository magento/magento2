<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

interface CartItemValidatorInterface
{
    /**
     * Validate cart item against the cart.
     *
     * @param CartInterface $cart
     * @param CartItemInterface $cartItem
     * @return CartItemValidatorResultInterface
     */
    public function validate(CartInterface $cart, CartItemInterface $cartItem): CartItemValidatorResultInterface;
}
