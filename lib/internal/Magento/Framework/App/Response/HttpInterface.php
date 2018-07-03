<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

/**
 * HTTP response interface
 *
 * @api
 */
interface HttpInterface extends \Magento\Framework\App\ResponseInterface
{
    /**
     * Set HTTP response code
     *
     * @param int $code
     * @return void
     */
    public function setHttpResponseCode($code);

    /**
     * Get HTTP response code
     *
     * @return int
     * @since 100.2.0
     */
    public function getHttpResponseCode();

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return self
     * @since 100.2.0
     */
    public function setHeader($name, $value, $replace = false);

    /**
     * Get header value by name
     *
     * Returns first found header by passed name.
     * If header with specified name was not found returns false.
     *
     * @param string $name
     * @return \Zend\Http\Header\HeaderInterface|bool
     * @since 100.2.0
     */
    public function getHeader($name);

    /**
     * Remove header by name from header stack
     *
     * @param string $name
     * @return self
     * @since 100.2.0
     */
    public function clearHeader($name);

    /**
     * Allow granular setting of HTTP response status code, version and phrase
     *
     * For example, a HTTP response as the following:
     *     HTTP 200 1.1 Your response has been served
     * Can be set with the arguments
     *     $httpCode = 200
     *     $version = 1.1
     *     $phrase = 'Your response has been served'
     *
     * @param int|string $httpCode
     * @param null|int|string $version
     * @param null|string $phrase
     * @return self
     * @since 100.2.0
     */
    public function setStatusHeader($httpCode, $version = null, $phrase = null);

    /**
     * Append the given string to the response body
     *
     * @param string $value
     * @return self
     * @since 100.2.0
     */
    public function appendBody($value);

    /**
     * Set the response body to the given value
     *
     * Any previously set contents will be replaced by the new content.
     *
     * @param string $value
     * @return self
     * @since 100.2.0
     */
    public function setBody($value);

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior redirects.
     *
     * @param string $url
     * @param int $code
     * @return self
     * @since 100.2.0
     */
    public function setRedirect($url, $code = 302);
}
