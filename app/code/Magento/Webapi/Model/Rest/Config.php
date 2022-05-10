<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Webapi\Model\Rest;

use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\ConfigInterface as ModelConfigInterface;
use Magento\Webapi\Model\Config\Converter;

/**
 * Webapi Config Model for Rest.
 */
class Config
{
    /**#@+
     * HTTP methods supported by REST.
     */
    public const HTTP_METHOD_GET = 'GET';
    public const HTTP_METHOD_DELETE = 'DELETE';
    public const HTTP_METHOD_PUT = 'PUT';
    public const HTTP_METHOD_POST = 'POST';
    public const HTTP_METHOD_PATCH = 'PATCH';
    /**#@-*/

    /**#@+
     * Keys that a used for config internal representation.
     */
    public const KEY_IS_SECURE = 'isSecure';
    public const KEY_CLASS = 'class';
    public const KEY_METHOD = 'method';
    public const KEY_ROUTE_PATH = 'routePath';
    public const KEY_ACL_RESOURCES = 'resources';
    public const KEY_PARAMETERS = 'parameters';
    public const KEY_INPUT_ARRAY_SIZE_LIMIT = 'input-array-size-limit';
    /*#@-*/

    /**
     * @var ModelConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Controller\Router\Route\Factory
     */
    protected $_routeFactory;

    /**
     * @param ModelConfigInterface $config
     * @param \Magento\Framework\Controller\Router\Route\Factory $routeFactory
     */
    public function __construct(
        ModelConfigInterface $config,
        \Magento\Framework\Controller\Router\Route\Factory $routeFactory
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
     *      'class' => \Magento\Catalog\Api\CategoryRepositoryInterface::class,
     *      'serviceMethod' => 'item'
     *      'secure' => true
     *  );</pre>
     * @return \Magento\Webapi\Controller\Rest\Router\Route
     */
    protected function _createRoute($routeData)
    {
        /** @var $route \Magento\Webapi\Controller\Rest\Router\Route */
        $route = $this->_routeFactory->createRoute(
            \Magento\Webapi\Controller\Rest\Router\Route::class,
            $routeData[self::KEY_ROUTE_PATH]
        );

        $route->setServiceClass($routeData[self::KEY_CLASS])
            ->setServiceMethod($routeData[self::KEY_METHOD])
            ->setSecure($routeData[self::KEY_IS_SECURE])
            ->setAclResources($routeData[self::KEY_ACL_RESOURCES])
            ->setParameters($routeData[self::KEY_PARAMETERS])
            ->setInputArraySizeLimit($routeData[self::KEY_INPUT_ARRAY_SIZE_LIMIT]);
        return $route;
    }

    /**
     * Get service base URL
     *
     * @param \Magento\Framework\Webapi\Rest\Request $request
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
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @return Route[] matched routes
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function getRestRoutes(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $requestHttpMethod = $request->getHttpMethod();
        $servicesRoutes = $this->_config->getServices()[Converter::KEY_ROUTES];
        $routes = [];
        // Return the route on exact match
        if (isset($servicesRoutes[$request->getPathInfo()][$requestHttpMethod])) {
            $methodInfo = $servicesRoutes[$request->getPathInfo()][$requestHttpMethod];
            $routes[] = $this->_createRoute(
                [
                    self::KEY_ROUTE_PATH => $request->getPathInfo(),
                    self::KEY_CLASS => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_CLASS],
                    self::KEY_METHOD => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_METHOD],
                    self::KEY_IS_SECURE => $methodInfo[Converter::KEY_SECURE],
                    self::KEY_ACL_RESOURCES => array_keys($methodInfo[Converter::KEY_ACL_RESOURCES]),
                    self::KEY_PARAMETERS => $methodInfo[Converter::KEY_DATA_PARAMETERS],
                    self::KEY_INPUT_ARRAY_SIZE_LIMIT => $methodInfo[Converter::KEY_INPUT_ARRAY_SIZE_LIMIT],
                ]
            );
            return $routes;
        }
        $serviceBaseUrl = $this->_getServiceBaseUrl($request);
        ksort($servicesRoutes, SORT_STRING);
        foreach ($servicesRoutes as $url => $httpMethods) {
            // skip if baseurl is not null and does not match
            if (!$serviceBaseUrl || strpos(trim($url, '/'), (string) trim($serviceBaseUrl, '/')) !== 0) {
                // base url does not match, just skip this service
                continue;
            }
            foreach ($httpMethods as $httpMethod => $methodInfo) {
                if (strtoupper($httpMethod) == strtoupper($requestHttpMethod)) {
                    $aclResources = array_keys($methodInfo[Converter::KEY_ACL_RESOURCES]);
                    $routes[] = $this->_createRoute(
                        [
                            self::KEY_ROUTE_PATH => $url,
                            self::KEY_CLASS => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_CLASS],
                            self::KEY_METHOD => $methodInfo[Converter::KEY_SERVICE][Converter::KEY_SERVICE_METHOD],
                            self::KEY_IS_SECURE => $methodInfo[Converter::KEY_SECURE],
                            self::KEY_ACL_RESOURCES => $aclResources,
                            self::KEY_PARAMETERS => $methodInfo[Converter::KEY_DATA_PARAMETERS],
                            self::KEY_INPUT_ARRAY_SIZE_LIMIT => $methodInfo[Converter::KEY_INPUT_ARRAY_SIZE_LIMIT],
                        ]
                    );
                }
            }
        }

        return $routes;
    }
}
