<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception for unknown or invalid PayPal IPN requests
 */
class UnknownIpnException extends LocalizedException
{
}
