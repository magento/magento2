<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Api\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Thrown when coupon codes requests limit is reached.
 *
 * @api
 */
class CodeRequestLimitException extends LocalizedException
{

}
