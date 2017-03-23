<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Cookie;

use \Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use \Magento\Framework\Stdlib\Test\Unit\Cookie\PhpCookieManagerTest;

/**
 * Mock global setcookie function
 *
 * @param string $name
 * @param string $value
 * @param int $expiry
 * @param string $path
 * @param string $domain
 * @param bool $secure
 * @param bool $httpOnly
 * @return bool
 */
function setcookie($name, $value, $expiry, $path, $domain, $secure, $httpOnly)
{
    global $mockTranslateSetCookie;

    if (isset($mockTranslateSetCookie) && $mockTranslateSetCookie === true) {
        PhpCookieManagerTest::$isSetCookieInvoked = true;
        return PhpCookieManagerTest::assertCookie($name, $value, $expiry, $path, $domain, $secure, $httpOnly);
    } else {
        return call_user_func_array(__FUNCTION__, func_get_args());
    }
}
