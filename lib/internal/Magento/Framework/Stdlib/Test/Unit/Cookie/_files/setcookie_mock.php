<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\Stdlib\Test\Unit\Cookie\PhpCookieManagerTest;

/**
 * Mock global setcookie function
 *
 * @param string $name
 * @param string $value
 * @param array $options
 * @return bool
 */
function setcookie($name, $value, $options)
{
    global $mockTranslateSetCookie;

    if (isset($mockTranslateSetCookie) && $mockTranslateSetCookie === true) {
        PhpCookieManagerTest::$isSetCookieInvoked = true;
        return PhpCookieManagerTest::assertCookie(
            $name,
            $value,
            $options['expires'],
            $options['path'],
            $options['domain'],
            $options['secure'],
            $options['httponly'],
            $options['samesite']
        );
    } else {
        // phpcs:ignore
        return call_user_func_array(__FUNCTION__, func_get_args());
    }
}
