<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api;

/**
 * Interface for managing payment gateway failures
 */
interface PaymentFailuresInterface
{
    /**
     * Handles payment gateway failures.
     *
     * @param int $cartId
     * @param string $errorMessage
     * @param string $checkoutType
     * @return PaymentFailuresInterface
     */
    public function handle(
        $cartId,
        $errorMessage,
        $checkoutType = 'onepage'
    );
}
