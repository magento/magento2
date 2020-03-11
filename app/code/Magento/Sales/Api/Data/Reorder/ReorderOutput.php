<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data\Reorder;

use Magento\Quote\Api\Data\CartInterface;

/**
 * DTO represent output for \Magento\Sales\Api\ReorderInterface
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
    private $lineItemErrors;

    /**
     * @param CartInterface $cart
     * @param array $lineItemErrors
     */
    public function __construct(CartInterface $cart, array $lineItemErrors)
    {
        $this->cart = $cart;
        $this->lineItemErrors = $lineItemErrors;
    }

    /**
     * @return CartInterface
     */
    public function getCart(): CartInterface
    {
        return $this->cart;
    }

    /**
     * @return LineItemError[]
     */
    public function getLineItemErrors(): array
    {
        return $this->lineItemErrors;
    }
}
