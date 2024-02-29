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
 * @since 100.0.2
 */
interface ClientInterface
{
    /**
     * Set request timeout
     *
     * @param int $value value in seconds
     * @return void
     */
    public function setTimeout(int $value): void;

    /**
     * Set request headers from hash
     *
     * @param array $headers an array of header names as keys and header values as values
     * @return void
     */
    public function setHeaders(array $headers): void;

    /**
     * Add header to request
     *
     * @param string $name name of the HTTP header
     * @param string $value value of the HTTP header
     * @return void
     */
    public function addHeader(string $name, string $value): void;

    /**
     * Remove header from request
     *
     * @param string $name name of the HTTP header
     * @return void
     */
    public function removeHeader(string $name): void;

    /**
     * Set login credentials for basic authentication.
     *
     * @param string $login user identity/name
     * @param string $pass user password
     * @return void
     */
    public function setCredentials(string $login, string $pass): void;

    /**
     * Add cookie to request
     *
     * @param string $name name of the cookie
     * @param string $value value of the cookie
     * @return void
     */
    public function addCookie(string $name, string $value): void;

    /**
     * Remove cookie from request
     *
     * @param string $name name of the cookie
     * @return void
     */
    public function removeCookie(string $name): void;

    /**
     * Set request cookies from hash
     *
     * @param array $cookies an array of cookies with cookie names as keys and cookie values as value
     * @return void
     */
    public function setCookies(array $cookies): void;

    /**
     * Remove cookies from request
     *
     * @return void
     */
    public function removeCookies(): void;

    /**
     * Make GET request
     *
     * @param string $uri full uri
     * @return void
     */
    public function get(string $uri): void;

    /**
     * Make DELETE request
     *
     * @param string $uri full uri
     * @return void
     */
    public function delete(string $uri): void;

    /**
     * Make POST request
     *
     * @param string $uri full uri
     * @param array|string $params POST fields array or string in case of JSON or XML data
     * @return void
     */
    public function post(string $uri, array|string $params): void;

    /**
     * Make PATCH request
     *
     * @param string $uri full uri
     * @param array|string $params PATCH fields array or string in case of JSON or XML data
     * @return void
     */
    public function patch(string $uri, array|string $params): void;

    /**
     * Make PUT request
     *
     * @param string $uri full uri
     * @param array|string $params PUT fields array or string in case of JSON or XML data
     * @return void
     */
    public function put(string $uri, array|string $params): void;

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Get response body
     *
     * @return string
     */
    public function getBody(): string;

    /**
     * Get response status code
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Get response cookies (k=>v)
     *
     * @return array
     */
    public function getCookies(): array;

    /**
     * Set additional option
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setOption(string $key, string $value): void;

    /**
     * Set additional options
     *
     * @param array $arr
     * @return void
     */
    public function setOptions(array $arr): void;
}
