<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Replacement for PhpCookieManager model that doesn't send cookie headers in testing environment
 */
namespace Magento\TestFramework;

class CookieManager extends \Magento\Framework\Stdlib\Cookie\PhpCookieManager
{
    /**
     * Dummy function, which sets value directly to $_COOKIE super-global array instead of calling setcookie()
     *
     * @param string $name
     * @param string $value
     * @param array $metadataArray
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setCookie($name, $value, array $metadataArray)
    {
        $_COOKIE[$name] = $value;
    }
}
