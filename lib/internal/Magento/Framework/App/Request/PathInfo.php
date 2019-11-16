<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Request;

/**
 * Computes path info and query string from request
 */
class PathInfo
{
    /**
     * Get path info using from the request URI and base URL
     *
     * @param string $requestUri
     * @param string $baseUrl
     * @return string
     */
    public function getPathInfo(string $requestUri, string $baseUrl) : string
    {
        if ($requestUri === '/') {
            return '';
        }

        $requestUri = $this->removeRepeatedSlashes($requestUri);
        $parsedRequestUri = explode('?', $requestUri, 2);
        $pathInfo = (string)substr(current($parsedRequestUri), (int)strlen($baseUrl));

        if ($this->isNoRouteUri($baseUrl, $pathInfo)) {
            $pathInfo = \Magento\Framework\App\Router\Base::NO_ROUTE;
        }
        return $pathInfo;
    }

    /**
     * Get query string using from the request URI
     *
     * @param string $requestUri
     * @return string
     */
    public function getQueryString(string $requestUri) : string
    {
        $requestUri = $this->removeRepeatedSlashes($requestUri);
        $parsedRequestUri = explode('?', $requestUri, 2);
        $queryString = !isset($parsedRequestUri[1]) ? '' : '?' . $parsedRequestUri[1];
        return $queryString;
    }

    /**
     * Remove repeated slashes from the start of the path.
     *
     * @param string $pathInfo
     * @return string
     */
    private function removeRepeatedSlashes($pathInfo) : string
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        if ($firstChar == '/') {
            $pathInfo = '/' . ltrim($pathInfo, '/');
        }

        return $pathInfo;
    }

    /**
     * Check is URI should be marked as no route, helps route to 404 URI like `index.phpadmin`.
     *
     * @param string $baseUrl
     * @param string $pathInfo
     * @return bool
     */
    private function isNoRouteUri($baseUrl, $pathInfo) : bool
    {
        $firstChar = (string)substr($pathInfo, 0, 1);
        return $baseUrl !== '' && !in_array($firstChar, ['/', '']);
    }
}
