<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Http;

interface RequestInterface
{
    /**
     * Returns whether request was delivered over HTTPS
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Retrieve a value from a cookie.
     *
     * @param string|null $name
     * @param string|null $default The default value to return if no value could be found for the given $name.
     * @return string|null
     */
    public function getCookie($name = null, $default = null);
}
