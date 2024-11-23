<?php
/************************************************************************
 *
 *  ADOBE CONFIDENTIAL
 *  ___________________
 *
 *  Copyright 2024 Adobe
 *  All Rights Reserved.
 *
 *  NOTICE: All information contained herein is, and remains
 *  the property of Adobe and its suppliers, if any. The intellectual
 *  and technical concepts contained herein are proprietary to Adobe
 *  and its suppliers and are protected by all applicable intellectual
 *  property laws, including trade secret and copyright laws.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from Adobe.
 *  ************************************************************************
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
