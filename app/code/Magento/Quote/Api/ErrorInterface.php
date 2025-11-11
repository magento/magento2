<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Api;

interface ErrorInterface
{
    /**
     * Get error code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Get cart item position
     *
     * @return int
     */
    public function getCartItemPosition(): int;

    /**
     * Get error message
     *
     * @return string
     */
    public function getMessage(): string;
}
