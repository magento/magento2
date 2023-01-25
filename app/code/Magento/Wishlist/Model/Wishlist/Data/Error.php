<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\Data;

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
     * @param string $message
     * @param string $code
     */
    public function __construct(string $message, string $code)
    {
        $this->message = $message;
        $this->code = $code;
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
}
