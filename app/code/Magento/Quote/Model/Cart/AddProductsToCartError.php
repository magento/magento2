<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\ErrorInterface;

/**
 * Create instances of errors on adding products to cart. Identify error code based on the message
 */
class AddProductsToCartError
{
    private const ERROR_UNDEFINED = 'UNDEFINED';

    /**
     * @param array $errorMessageCodesMapper
     */
    public function __construct(
        private readonly array $errorMessageCodesMapper
    ) {
    }

    /**
     * Returns an error object
     *
     * @param string $message
     * @param int $cartItemPosition
     * @param float $stockItemQuantity
     * @return Data\Error
     */
    public function create(
        string $message,
        int $cartItemPosition = 0,
        float $stockItemQuantity = 0.0
    ): ErrorInterface {

        return new Data\InsufficientStockError(
            $message,
            $this->getErrorCode($message),
            $cartItemPosition,
            $stockItemQuantity
        );
    }

    /**
     * Get message error code.
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        $message = preg_replace('/\d+/', '%s', $message);
        foreach ($this->errorMessageCodesMapper as $codeMessage => $code) {
            if (false !== stripos($message, $codeMessage)) {
                return $code;
            }
        }

        /* If no code was matched, return the default one */
        return self::ERROR_UNDEFINED;
    }
}
