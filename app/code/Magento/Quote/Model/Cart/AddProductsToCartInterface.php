<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Cart\Data\AddProductsToCartOutput;

/**
 * Unified approach to add products to the Shopping Cart.
 * Client code must validate, that customer is eligible to call service with provided {cartId} and {cartItems}
 */
interface AddProductsToCartInterface
{
    /**
     * Add cart items to the cart
     *
     * @param string $cartId
     * @param Data\CartItem[] $cartItems
     * @return AddProductsToCartOutput
     * @throws NoSuchEntityException Could not find a Cart with provided $maskedCartId
     */
    public function execute(string $cartId, array $cartItems): AddProductsToCartOutput;
}
