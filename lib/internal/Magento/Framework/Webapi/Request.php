<?php
/**
 * Web API request.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request as HttpRequest;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;

class Request extends HttpRequest implements RequestInterface
{
    /**
     * Modify pathInfo: strip down the front name and query parameters.
     *
     * @param AreaList $areaList
     * @param ScopeInterface $configScope
     * @param CookieReaderInterface $cookieReader
     * @param null|string|\Zend_Uri $uri
     */
    public function __construct(
        CookieReaderInterface $cookieReader,
        AreaList $areaList,
        ScopeInterface $configScope,
        $uri = null
    ) {
        parent::__construct($cookieReader, $uri);

        $pathInfo = $this->getRequestUri();
        /** Remove base url and area from path */
        $areaFrontName = $areaList->getFrontName($configScope->getCurrentScope());
        $pathInfo = preg_replace("#.*?/{$areaFrontName}/?#", '/', $pathInfo);
        /** Remove GET parameters from path */
        $pathInfo = preg_replace('#\?.*#', '', $pathInfo);
        $this->setPathInfo($pathInfo);
    }

    /**
     * {@inheritdoc}
     *
     * Added CGI environment support.
     */
    public function getHeader($header, $default = false)
    {
        $headerValue = parent::getHeader($header, $default);
        if ($headerValue == false) {
            /** Workaround for hhvm environment */
            $header = 'REDIRECT_HTTP_' . strtoupper(str_replace('-', '_', $header));
            if (isset($_SERVER[$header])) {
                $headerValue = $_SERVER[$header];
            }
        }
        return $headerValue;
    }
}
