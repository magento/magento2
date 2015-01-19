<?php
/**
 * Web API request.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller;

class Request extends \Zend_Controller_Request_Http implements \Magento\Framework\App\RequestInterface
{
    /** @var int */
    protected $_consumerId = 0;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieReaderInterface
     */
    protected $_cookieReader;

    /**
     * Modify pathInfo: strip down the front name and query parameters.
     *
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Stdlib\Cookie\CookieReaderInterface $cookieReader
     * @param null|string|\Zend_Uri $uri
     */
    public function __construct(
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Stdlib\Cookie\CookieReaderInterface $cookieReader,
        $uri = null
    ) {
        parent::__construct($uri);
        $areaFrontName = $areaList->getFrontName($configScope->getCurrentScope());
        $this->_pathInfo = $this->_requestUri;
        /** Remove base url and area from path */
        $this->_pathInfo = preg_replace("#.*?/{$areaFrontName}/?#", '/', $this->_pathInfo);
        /** Remove GET parameters from path */
        $this->_pathInfo = preg_replace('#\?.*#', '', $this->_pathInfo);
        $this->_cookieReader = $cookieReader;
    }

    /**
     * Retrieve a value from a cookie.
     *
     * @param string|null $name
     * @param string|null $default The default value to return if no value could be found for the given $name.
     * @return string|null
     */
    public function getCookie($name = null, $default = null)
    {
        return $this->_cookieReader->getCookie($name, $default);
    }

    /**
     * {@inheritdoc}
     *
     * Added CGI environment support.
     */
    public function getHeader($header)
    {
        $headerValue = parent::getHeader($header);
        if ($headerValue == false) {
            /** Workaround for php-fpm environment */
            $header = strtoupper(str_replace('-', '_', $header));
            if (isset($_SERVER[$header]) && in_array($header, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerValue = $_SERVER[$header];
            }
        }
        return $headerValue;
    }
}
