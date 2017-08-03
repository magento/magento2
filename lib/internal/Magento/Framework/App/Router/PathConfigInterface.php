<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

/**
 * Interface \Magento\Framework\App\Router\PathConfigInterface
 *
 * @since 2.0.0
 */
interface PathConfigInterface
{
    /**
     * Retrieve secure url for current request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     * @since 2.0.0
     */
    public function getCurrentSecureUrl(\Magento\Framework\App\RequestInterface $request);

    /**
     * Check whether given path should be secure according to configuration security requirements for URL
     * "Secure" should not be confused with https protocol, it is about web/secure/*_url settings usage only
     *
     * @param string $path
     * @return bool
     * @since 2.0.0
     */
    public function shouldBeSecure($path);

    /**
     * Get router default request path
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultPath();
}
