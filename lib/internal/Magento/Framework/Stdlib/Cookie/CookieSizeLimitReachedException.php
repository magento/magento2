<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\Exception\LocalizedException;

/**
 * CookieSizeLimitReachedException is thrown when detecting that a browser limit, or potential browser limit has been
 * reached regarding cookie limits.
 *
 * Limits can include the amount of data stored in an individual cookie as well as the number of cookies
 * set for the domain.
 *
 * @api
 * @since 100.0.2
 */
class CookieSizeLimitReachedException extends LocalizedException
{
}
