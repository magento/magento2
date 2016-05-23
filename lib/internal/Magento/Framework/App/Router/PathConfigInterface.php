<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

interface PathConfigInterface
{
    /**
     * Retrieve secure url for current request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    public function getCurrentSecureUrl(\Magento\Framework\App\RequestInterface $request);

    /**
     * Check whether given path should be secure according to configuration security requirements for URL
     * "Secure" should not be confused with https protocol, it is about web/secure/*_url settings usage only
     *
     * @param string $path
     * @return bool
     */
    public function shouldBeSecure($path);

    /**
     * Get router default request path
     *
     * @return string
     */
    public function getDefaultPath();
}
