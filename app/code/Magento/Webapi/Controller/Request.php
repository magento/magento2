<?php
/**
 * Web API request.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request as HttpRequest;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;

class Request extends HttpRequest implements RequestInterface
{
    /** @var int */
    protected $_consumerId = 0;

    /**
     * @var CookieReaderInterface
     */
    protected $_cookieReader;

    /**
     * Modify pathInfo: strip down the front name and query parameters.
     *
     * @param AreaList $areaList
     * @param ScopeInterface $configScope
     * @param CookieReaderInterface $cookieReader
     * @param null|string|\Zend_Uri $uri
     */
    public function __construct(
        AreaList $areaList,
        ScopeInterface $configScope,
        CookieReaderInterface $cookieReader,
        $uri = null
    ) {
        parent::__construct($uri);
        $this->_cookieReader = $cookieReader;

        $pathInfo = $this->getRequestUri();
        /** Remove base url and area from path */
        $areaFrontName = $areaList->getFrontName($configScope->getCurrentScope());
        $pathInfo = preg_replace("#.*?/{$areaFrontName}/?#", '/', $pathInfo);
        /** Remove GET parameters from path */
        $pathInfo = preg_replace('#\?.*#', '', $pathInfo);
        $this->setPathInfo($pathInfo);
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
    public function getHeader($name, $default = false)
    {
        $headerValue = parent::getHeader($name);
        if ($headerValue == false) {
            /** Workaround for php-fpm environment */
            $header = strtoupper(str_replace('-', '_', $name));
            if (isset($_SERVER[$header]) && in_array($header, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerValue = $_SERVER[$header];
            }
        }
        return $headerValue;
    }
}
