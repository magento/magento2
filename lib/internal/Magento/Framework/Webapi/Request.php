<?php
/**
 * Web API request.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request as HttpRequest;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Class \Magento\Framework\Webapi\Request
 *
 * @since 2.0.0
 */
class Request extends HttpRequest implements RequestInterface
{
    /**
     * Name of query parameter to specify services for which to generate schema
     */
    const REQUEST_PARAM_SERVICES = 'services';

    /**
     * services parameter value to indicate that a schema for all services should be generated
     */
    const ALL_SERVICES = 'all';

    /**
     * Modify pathInfo: strip down the front name and query parameters.
     *
     * @param CookieReaderInterface $cookieReader
     * @param StringUtils $converter
     * @param AreaList $areaList
     * @param ScopeInterface $configScope
     * @param null|string|\Zend_Uri $uri
     * @since 2.0.0
     */
    public function __construct(
        CookieReaderInterface $cookieReader,
        StringUtils $converter,
        AreaList $areaList,
        ScopeInterface $configScope,
        $uri = null
    ) {
        parent::__construct($cookieReader, $converter, $uri);

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
     * @since 2.0.0
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

    /**
     * Identify versions of resources that should be used for API configuration generation.
     *
     * @param string|null $default
     * @return array|string
     * @throws \Magento\Framework\Webapi\Exception When GET parameters are invalid
     * @since 2.0.0
     */
    public function getRequestedServices($default = null)
    {
        $param = $this->getParam(self::REQUEST_PARAM_SERVICES, $default);
        return $this->_convertRequestParamToServiceArray($param);
    }

    /**
     * Extract the resources query param value and return associative array of the form 'resource' => 'version'
     *
     * @param string $param eg <pre> testModule1AllSoapAndRestV1,testModule2AllSoapNoRestV1 </pre>
     * @return string|array <pre> eg array (
     *      'testModule1AllSoapAndRestV1',
     *      'testModule2AllSoapNoRestV1',
     *      )</pre>
     * @throws \Magento\Framework\Webapi\Exception
     * @since 2.0.0
     */
    protected function _convertRequestParamToServiceArray($param)
    {
        $serviceSeparator = ',';
        $serviceVerPattern = "[a-zA-Z\d]*V[\d]+";
        $regexp = "/^({$serviceVerPattern})([{$serviceSeparator}]{$serviceVerPattern})*\$/";
        if ($param == 'all') {
            return $param;
        }
        //Check if the $param is of valid format
        if (empty($param) || !preg_match($regexp, $param)) {
            $message = new Phrase('Incorrect format of request URI or Requested services are missing.');
            throw new \Magento\Framework\Webapi\Exception($message);
        }
        //Split the $param string to create an array of 'service' => 'version'
        $serviceVersionArray = explode($serviceSeparator, $param);
        $serviceArray = [];
        foreach ($serviceVersionArray as $service) {
            $serviceArray[] = $service;
        }
        return $serviceArray;
    }
}
