<?php
/**
 * HTTP response interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

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
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return self
     */
    public function setHeader($name, $value, $replace = false);

    /**
     * @param int|string $httpCode
     * @param null|int|string $version
     * @param null|string $phrase
     * @return self
     */
    public function setStatusHeader($httpCode, $version = null, $phrase = null);

    /**
     * @param string $value
     * @return self
     */
    public function appendBody($value);

    /**
     * @param string $value
     * @return self
     */
    public function setBody($value);

    /**
     * Set redirect URL
     *
     * Sets Location header and response code. Forces replacement of any prior
     * redirects.
     *
     * @param string $url
     * @param int $code
     * @return self
     */
    public function setRedirect($url, $code = 302);
}
