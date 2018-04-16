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
 */
interface ClientInterface
{
    /**
     * Set request timeout
     *
     * @param int $value value in seconds
     * @return void
     */
    public function setTimeout($value);

    /**
     * Set request headers from hash
     *
     * @param array $headers an array of header names as keys and header values as values
     * @return void
     */
    public function setHeaders($headers);

    /**
     * Add header to request
     *
     * @param string $name name of the HTTP header
     * @param string $value value of the HTTP header
     * @return void
     */
    public function addHeader($name, $value);

    /**
     * Remove header from request
     *
     * @param string $name name of the HTTP header
     * @return void
     */
    public function removeHeader($name);

    /**
     * Set login credentials for basic authentication.
     *
     * @param string $login user identity/name
     * @param string $pass user password
     * @return void
     */
    public function setCredentials($login, $pass);

    /**
     * Add cookie to request
     *
     * @param string $name name of the cookie
     * @param string $value value of the cookie
     * @return void
     */
    public function addCookie($name, $value);

    /**
     * Remove cookie from request
     *
     * @param string $name name of the cookie
     * @return void
     */
    public function removeCookie($name);

    /**
     * Set request cookies from hash
     *
     * @param array $cookies an array of cookies with cookie names as keys and cookie values as value
     * @return void
     */
    public function setCookies($cookies);

    /**
     * Remove cookies from request
     *
     * @return void
     */
    public function removeCookies();

    /**
     * Make GET request
     *
     * @param string $uri full uri
     * @return array
     */
    public function get($uri);

    /**
     * Make POST request
     *
     * @param string $uri full uri
     * @param array|string $params POST fields array or string in case of JSON or XML data
     * @return void
     */
    public function post($uri, $params);

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Get response body
     *
     * @return string
     */
    public function getBody();

    /**
     * Get response status code
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get response cookies (k=>v)
     *
     * @return array
     */
    public function getCookies();

    /**
     * Set additional option
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setOption($key, $value);

    /**
     * Set additional options
     *
     * @param array $arr
     * @return void
     */
    public function setOptions($arr);
}
