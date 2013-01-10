<?php
/**
 * Router for Magento web API.
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
class Mage_Webapi_Controller_Router_Rest
{
    /** @var array */
    protected $_routes = array();

    /** @var Mage_Webapi_Helper_Data */
    protected $_helper;

    /** @var Mage_Webapi_Model_Config_Rest */
    protected $_apiConfig;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Helper_Data $helper
     * @param Mage_Webapi_Model_Config_Rest $apiConfig
     */
    public function __construct(
        Mage_Webapi_Helper_Data $helper,
        Mage_Webapi_Model_Config_Rest $apiConfig
    ) {
        $this->_helper = $helper;
        $this->_apiConfig = $apiConfig;
    }

    /**
     * Route the Request, the only responsibility of the class.
     * Find route that matches current URL, set parameters of the route to Request object.
     *
     * @param Mage_Webapi_Controller_Request_Rest $request
     * @return Mage_Webapi_Controller_Router_Route_Rest
     * @throws Mage_Webapi_Exception
     */
    public function match(Mage_Webapi_Controller_Request_Rest $request)
    {
        /** @var Mage_Webapi_Controller_Router_Route_Rest[] $routes */
        $routes = $this->_apiConfig->getAllRestRoutes();
        foreach ($routes as $route) {
            $params = $route->match($request);
            if ($params !== false) {
                $request->setParams($params);
                /** Initialize additional request parameters using data from route */
                $request->setResourceName($route->getResourceName());
                $request->setResourceType($route->getResourceType());
                return $route;
            }
        }
        throw new Mage_Webapi_Exception($this->_helper->__('Request does not match any route.'),
            Mage_Webapi_Exception::HTTP_NOT_FOUND);
    }

    /**
     * Check whether current request matches any route of specified method or not. Method version is taken into account.
     *
     * @param Mage_Webapi_Controller_Request_Rest $request
     * @param string $methodName
     * @param string $version
     * @throws Mage_Webapi_Exception In case when request does not match any route of specified method.
     */
    public function checkRoute(Mage_Webapi_Controller_Request_Rest $request, $methodName, $version)
    {
        $resourceName = $request->getResourceName();
        $routes = $this->_apiConfig->getMethodRestRoutes($resourceName, $methodName, $version);
        foreach ($routes as $route) {
            if ($route->match($request)) {
                return;
            }
        }
        throw new Mage_Webapi_Exception($this->_helper->__('Request does not match any route.'),
            Mage_Webapi_Exception::HTTP_NOT_FOUND);
    }
}
