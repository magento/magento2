<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Api\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Thrown when too many payment processing requests have been initiated by a user.
 */
class PaymentProcessingRateLimitExceededException extends LocalizedException
{

}
