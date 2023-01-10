<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\Data;

use Magento\Quote\Api\Data\CartInterface;

/**
 * DTO represents output for \Magento\Quote\Model\Cart\AddProductsToCart
 */
class AddProductsToCartOutput
{
    /**
     * @var CartInterface
     */
    private $cart;

    /**
     * @var Error[]
     */
    private $errors;

    /**
     * @param CartInterface $cart
     * @param Error[] $errors
     */
    public function __construct(CartInterface $cart, array $errors)
    {
        $this->cart = $cart;
        $this->errors = $errors;
    }

    /**
     * Get Shopping Cart
     *
     * @return CartInterface
     */
    public function getCart(): CartInterface
    {
        return $this->cart;
    }

    /**
     * Get errors happened during adding item to the cart
     *
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
