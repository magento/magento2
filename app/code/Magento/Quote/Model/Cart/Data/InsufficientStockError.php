<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\Data;

class InsufficientStockError extends Error
{
    /**
     * @var float
     */
    private float $quantity;

    /**
     * @param string $message
     * @param string $code
     * @param int $cartItemPosition
     * @param float $quantity
     */
    public function __construct(
        string $message,
        string $code,
        int $cartItemPosition,
        float $quantity
    ) {
        $this->quantity = $quantity;
        parent::__construct($message, $code, $cartItemPosition);
    }

    /**
     * Get Stock quantity
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }
}
