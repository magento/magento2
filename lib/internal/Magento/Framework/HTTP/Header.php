<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP;

/**
 * Library for working with HTTP headers
 */
class Header
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_converter;

    /**
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Framework\Stdlib\StringUtils $converter
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Stdlib\StringUtils $converter
    ) {
        $this->_request = $httpRequest;
        $this->_converter = $converter;
    }

    /**
     * Retrieve HTTP HOST
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpHost($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_HOST', $clean);
    }

    /**
     * Retrieve HTTP USER AGENT
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpUserAgent($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_USER_AGENT', $clean);
    }

    /**
     * Retrieve HTTP ACCEPT LANGUAGE
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpAcceptLanguage($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_ACCEPT_LANGUAGE', $clean);
    }

    /**
     * Retrieve HTTP ACCEPT CHARSET
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpAcceptCharset($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_ACCEPT_CHARSET', $clean);
    }

    /**
     * Retrieve HTTP REFERER
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getHttpReferer($clean = true)
    {
        return $this->_getHttpCleanValue('HTTP_REFERER', $clean);
    }

    /**
     * Returns the REQUEST_URI taking into account
     * platform differences between Apache and IIS
     *
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    public function getRequestUri($clean = false)
    {
        $uri = $this->_request->getRequestUri();
        if ($clean) {
            $uri = $this->_converter->cleanString($uri);
        }
        return $uri;
    }

    /**
     * Retrieve HTTP "clean" value
     *
     * @param string $var
     * @param boolean $clean clean non UTF-8 characters
     * @return string
     */
    protected function _getHttpCleanValue($var, $clean = true)
    {
        $value = $this->_request->getServer($var, '');
        if ($clean) {
            $value = $this->_converter->cleanString($value);
        }

        return $value;
    }
}
