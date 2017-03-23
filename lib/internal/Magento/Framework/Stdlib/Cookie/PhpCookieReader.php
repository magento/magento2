<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\Cookie;

/**
 * PhpCookieReader is an implementation of CookieReaderInterface that reads cookie data from the php $_COOKIE array.
 */
class PhpCookieReader implements CookieReaderInterface
{
    /**
     * Retrieve a value from a cookie.
     *
     * @param string $name
     * @param string|null $default The default value to return if no value could be found for the given $name.
     * @return string|null
     */
    public function getCookie($name, $default = null)
    {
        return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : $default;
    }
}
