<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Api;

use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;

/**
 * Limits number of times a user can initiate payment processing.
 *
 * @api
 */
interface PaymentProcessingRateLimiterInterface
{
    /**
     * Limit an attempt to initiate a new payment processing.
     *
     * @return void
     * @throws PaymentProcessingRateLimitExceededException
     */
    public function limit(): void;
}
