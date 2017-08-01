<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP;

/**
 * Interface for HTTP clients
 *
 * @api
 * @since 2.0.0
 */
interface ClientInterface
{
    /**
     * Set request timeout
     *
     * @param int $value value in seconds
     * @return void
     * @since 2.0.0
     */
    public function setTimeout($value);

    /**
     * Set request headers from hash
     *
     * @param array $headers an array of header names as keys and header values as values
     * @return void
     * @since 2.0.0
     */
    public function setHeaders($headers);

    /**
     * Add header to request
     *
     * @param string $name name of the HTTP header
     * @param string $value value of the HTTP header
     * @return void
     * @since 2.0.0
     */
    public function addHeader($name, $value);

    /**
     * Remove header from request
     *
     * @param string $name name of the HTTP header
     * @return void
     * @since 2.0.0
     */
    public function removeHeader($name);

    /**
     * Set login credentials for basic authentication.
     *
     * @param string $login user identity/name
     * @param string $pass user password
     * @return void
     * @since 2.0.0
     */
    public function setCredentials($login, $pass);

    /**
     * Add cookie to request
     *
     * @param string $name name of the cookie
     * @param string $value value of the cookie
     * @return void
     * @since 2.0.0
     */
    public function addCookie($name, $value);

    /**
     * Remove cookie from request
     *
     * @param string $name name of the cookie
     * @return void
     * @since 2.0.0
     */
    public function removeCookie($name);

    /**
     * Set request cookies from hash
     *
     * @param array $cookies an array of cookies with cookie names as keys and cookie values as value
     * @return void
     * @since 2.0.0
     */
    public function setCookies($cookies);

    /**
     * Remove cookies from request
     *
     * @return void
     * @since 2.0.0
     */
    public function removeCookies();

    /**
     * Make GET request
     *
     * @param string $uri full uri
     * @return array
     * @since 2.0.0
     */
    public function get($uri);

    /**
     * Make POST request
     *
     * @param string $uri full uri
     * @param array $params POST fields array
     * @return void
     * @since 2.0.0
     */
    public function post($uri, $params);

    /**
     * Get response headers
     *
     * @return array
     * @since 2.0.0
     */
    public function getHeaders();

    /**
     * Get response body
     *
     * @return string
     * @since 2.0.0
     */
    public function getBody();

    /**
     * Get response status code
     *
     * @return int
     * @since 2.0.0
     */
    public function getStatus();

    /**
     * Get response cookies (k=>v)
     *
     * @return array
     * @since 2.0.0
     */
    public function getCookies();

    /**
     * Set additional option
     *
     * @param string $key
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    public function setOption($key, $value);

    /**
     * Set additional options
     *
     * @param array $arr
     * @return void
     * @since 2.0.0
     */
    public function setOptions($arr);
}
