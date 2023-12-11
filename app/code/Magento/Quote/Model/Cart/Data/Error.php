<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\Data;

/**
 * DTO represents error item
 */
class Error
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $code;

    /**
     * @var int
     */
    private $cartItemPosition;

    /**
     * @param string $message
     * @param string $code
     * @param int $cartItemPosition
     */
    public function __construct(string $message, string $code, int $cartItemPosition)
    {
        $this->message = $message;
        $this->code = $code;
        $this->cartItemPosition = $cartItemPosition;
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
