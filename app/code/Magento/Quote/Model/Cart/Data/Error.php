<?php
/************************************************************************
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
