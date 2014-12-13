<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\Exception\LocalizedException;

/**
 * CookieSizeLimitReachedException is thrown when detecting that a browser limit, or potential browser limit has been
 * reached regarding cookie limits.
 *
 * Limits can include the amount of data stored in an individual cookie as well as the number of cookies
 * set for the domain.
 */
class CookieSizeLimitReachedException extends LocalizedException
{
}
