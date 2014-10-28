<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Rest;

use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Config\Converter;
use Magento\Webapi\Model\Config as ModelConfig;

/**
 * Webapi Config Model for Rest.
 */
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
    const KEY_ACL_RESOURCES = 'resources';
    const KEY_PARAMETERS = 'parameters';
    /*#@-*/

    /** @var ModelConfig */
    protected $_config;

    /** @var \Magento\Framework\Controller\Router\Route\Factory */
    protected $_routeFactory;

    /**
     * @param ModelConfig $config
     * @param \Magento\Framework\Controller\Router\Route\Factory $routeFactory
     */
    public function __construct(ModelConfig $config, \Magento\Framework\Controller\Router\Route\Factory $routeFactory)
    {
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
            $this->_formatRoutePath($routeData[self::KEY_ROUTE_PATH])
        );

        $route->setServiceClass($routeData[self::KEY_CLASS])
            ->setServiceMethod($routeData[self::KEY_METHOD])
            ->setSecure($routeData[self::KEY_IS_SECURE])
            ->setAclResources($routeData[self::KEY_ACL_RESOURCES])
            ->setParameters($routeData[self::KEY_PARAMETERS]);
        return $route;
    }

    /**
     * Lowercase all parts of the given route path except for the path parameters.
     *
     * @param string $routePath The route path (e.g. '/V1/Categories/:categoryId')
     * @return string The modified route path (e.g. '/v1/categories/:categoryId')
     */
    protected function _formatRoutePath($routePath)
    {
        $routePathParts = explode('/', $routePath);
        $pathParts = array();
        foreach ($routePathParts as $pathPart) {
            $pathParts[] = substr($pathPart, 0, 1) === ":" ? $pathPart : strtolower($pathPart);
        }
        return implode('/', $pathParts);
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
     * TODO: Refactor $this->_config->getServices() to return array with baseUrl as the key since its unique and
     *       needs to be used directly instead of looping each key
     * Generate the list of available REST routes. Current HTTP method is taken into account.
     *
     * @param \Magento\Webapi\Controller\Rest\Request $request
     * @return Route[]
     * @throws \Magento\Webapi\Exception
     */
    public function getRestRoutes(\Magento\Webapi\Controller\Rest\Request $request)
    {
        $serviceBaseUrl = $this->_getServiceBaseUrl($request);
        $requestHttpMethod = $request->getHttpMethod();
        $routes = array();
        $servicesRoutes = $this->_config->getServices()[Converter::KEY_ROUTES];
        ksort($servicesRoutes, SORT_STRING);
        foreach ($servicesRoutes as $url => $httpMethods) {
            // skip if baseurl is not null and does not match
            if (!$serviceBaseUrl || strpos(trim($url, '/'), trim($serviceBaseUrl, '/')) !== 0) {
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
                        ]
                    );
                }
            }
        }

        return $routes;
    }
}
