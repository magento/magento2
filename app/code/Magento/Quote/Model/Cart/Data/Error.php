<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\Data;

use Magento\Quote\Api\ErrorInterface;

/**
 * DTO represents error item
 */
class Error implements ErrorInterface
{
    /**
     * @param string $message
     * @param string $code
     * @param int $cartItemPosition
     */
    public function __construct(
        private readonly string $message,
        private readonly string $code,
        private readonly int $cartItemPosition
    ) {
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get error code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get cart item position
     *
     * @return int
     */
    public function getCartItemPosition(): int
    {
        return $this->cartItemPosition;
    }
}
