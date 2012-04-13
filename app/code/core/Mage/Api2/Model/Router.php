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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice api2 router model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Router
{
    /**
     * Routes which are stored in module config files api2.xml
     *
     * @var array
     */
    protected $_routes = array();

    /**
     * Set routes
     *
     * @param array $routes
     * @return Mage_Api2_Model_Router
     */
    public function setRoutes(array $routes)
    {
        $this->_routes = $routes;

        return $this;
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * Route the Request, the only responsibility of the class
     * Find route that match current URL, set parameters of the route to Request object
     *
     * @param Mage_Api2_Model_Request $request
     * @return Mage_Api2_Model_Request
     * @throws Mage_Api2_Exception
     */
    public function route(Mage_Api2_Model_Request $request)
    {
        $isMatched = false;

        /** @var $route Mage_Api2_Model_Route_Interface */
        foreach ($this->getRoutes() as $route) {
            if ($params = $route->match($request)) {
                $request->setParams($params);
                $isMatched = true;
                break;
            }
        }
        if (!$isMatched) {
            throw new Mage_Api2_Exception('Request does not match any route.', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        }
        if (!$request->getResourceType() || !$request->getModel()) {
            throw new Mage_Api2_Exception('Matched resource is not properly set.',
                Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        return $request;
    }

    /**
     * Set API type to request as a result of one pass route
     *
     * @param Mage_Api2_Model_Request $request
     * @param boolean $trimApiTypePath OPTIONAL If TRUE - /api/:api_type part of request path info will be trimmed
     * @return Mage_Api2_Model_Router
     * @throws Mage_Api2_Exception
     */
    public function routeApiType(Mage_Api2_Model_Request $request, $trimApiTypePath = true)
    {
        /** @var $apiTypeRoute Mage_Api2_Model_Route_ApiType */
        $apiTypeRoute = Mage::getModel('Mage_Api2_Model_Route_ApiType');

        if (!($apiTypeMatch = $apiTypeRoute->match($request, true))) {
            throw new Mage_Api2_Exception('Request does not match type route.', Mage_Api2_Model_Server::HTTP_NOT_FOUND);
        }
        // Trim matched URI path for next routes
        if ($trimApiTypePath) {
            $matchedPathLength = strlen('/' . ltrim($apiTypeRoute->getMatchedPath(), '/'));

            $request->setPathInfo(substr($request->getPathInfo(), $matchedPathLength));
        }
        $request->setParam('api_type', $apiTypeMatch['api_type']);

        return $this;
    }
}
