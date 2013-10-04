<?php
/**
 * Webapi Config Model for Rest.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Rest;

class Config
{
    /**#@+
     * HTTP methods supported by REST.
     */
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_POST = 'POST';
    /**#@-*/

    /**#@+
     * Keys that a used for config internal representation.
     */
    const KEY_IS_SECURE = 'isSecure';
    const KEY_CLASS = 'class';
    const KEY_METHOD = 'method';
    const KEY_ROUTE_PATH = 'routePath';
    /*#@-*/

    /** @var \Magento\Webapi\Model\Config  */
    protected $_config;

    /** @var \Magento\Controller\Router\Route\Factory */
    protected $_routeFactory;

    /**
     * @param \Magento\Webapi\Model\Config
     * @param \Magento\Controller\Router\Route\Factory $routeFactory
     */
    public function __construct(
        \Magento\Webapi\Model\Config $config,
        \Magento\Controller\Router\Route\Factory $routeFactory
    ) {
        $this->_config = $config;
        $this->_routeFactory = $routeFactory;
    }

    /**
     * Create route object.
     *
     * @param array $routeData Expected format:
     *  <pre>array(
     *      'routePath' => '/categories/:categoryId',
     *      'class' => 'Magento\Catalog\Service\CategoryService',
     *      'serviceMethod' => 'item'
     *      'secure' => true
     *  );</pre>
     * @return \Magento\Webapi\Controller\Rest\Router\Route
     */
    protected function _createRoute($routeData)
    {
        /** @var $route \Magento\Webapi\Controller\Rest\Router\Route */
        $route = $this->_routeFactory->createRoute(
            'Magento\Webapi\Controller\Rest\Router\Route',
            strtolower($routeData[self::KEY_ROUTE_PATH])
        );

        $route->setServiceClass($routeData[self::KEY_CLASS])
            ->setServiceMethod($routeData[self::KEY_METHOD])
            ->setSecure($routeData[self::KEY_IS_SECURE]);
        return $route;
    }

    /**
     * Get service base URL
     *
     * @param \Magento\Webapi\Controller\Rest\Request $request
     * @return string|null
     */
    protected function _getServiceBaseUrl($request)
    {
        $baseUrlRegExp = '#^/?\w+/\w+#';
        $serviceBaseUrl = preg_match($baseUrlRegExp, $request->getPathInfo(), $matches) ? $matches[0] : null;

        return $serviceBaseUrl;
    }

    /**
     * Generate the list of available REST routes. Current HTTP method is taken into account.
     *
     * @param \Magento\Webapi\Controller\Rest\Request $request
     * @return array
     * @throws \Magento\Webapi\Exception
     */
    public function getRestRoutes(\Magento\Webapi\Controller\Rest\Request $request)
    {
        $serviceBaseUrl = $this->_getServiceBaseUrl($request);
        $httpMethod = $request->getHttpMethod();
        $routes = array();
        foreach ($this->_config->getServices() as $serviceName => $serviceData) {
            // skip if baseurl is not null and does not match
            if (
                !isset($serviceData[\Magento\Webapi\Model\Config::ATTR_SERVICE_PATH])
                || !$serviceBaseUrl
                || strcasecmp(
                    trim($serviceBaseUrl, '/'),
                    trim($serviceData[\Magento\Webapi\Model\Config::ATTR_SERVICE_PATH], '/')
                ) !== 0
            ) {
                // baseurl does not match, just skip this service
                continue;
            }
            foreach ($serviceData['methods'] as $methodName => $methodInfo) {
                if (strtoupper($methodInfo[\Magento\Webapi\Model\Config::ATTR_HTTP_METHOD])
                    == strtoupper($httpMethod)) {
                    $secure = isset($methodInfo[\Magento\Webapi\Model\Config::ATTR_IS_SECURE])
                        ? $methodInfo[\Magento\Webapi\Model\Config::ATTR_IS_SECURE] : false;
                    $methodRoute = isset($methodInfo['route']) ? $methodInfo['route'] : '';
                    $routes[] = $this->_createRoute(
                        array(
                            self::KEY_ROUTE_PATH =>
                                $serviceData[\Magento\Webapi\Model\Config::ATTR_SERVICE_PATH] . $methodRoute,
                            self::KEY_CLASS => $serviceName,
                            self::KEY_METHOD => $methodName,
                            self::KEY_IS_SECURE => $secure
                        )
                    );
                }
            }
        }

        return $routes;
    }
}
