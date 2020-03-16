<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Reorder\Data;

use Magento\Quote\Api\Data\CartInterface;

/**
 * DTO represent output for \Magento\Sales\Model\Reorder\Reorder
 */
class ReorderOutput
{
    /**
     * @var CartInterface
     */
    private $cart;

    /**
     * @var array
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
     * @return CartInterface
     */
    public function getCart(): CartInterface
    {
        return $this->cart;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
