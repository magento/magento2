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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller\Rest;

class Router
{
    /** @var array */
    protected $_routes = array();

    /** @var \Magento\Webapi\Model\Rest\Config */
    protected $_apiConfig;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Model\Rest\Config $apiConfig
     */
    public function __construct(\Magento\Webapi\Model\Rest\Config $apiConfig)
    {
        $this->_apiConfig = $apiConfig;
    }

    /**
     * Route the Request, the only responsibility of the class.
     * Find route that matches current URL, set parameters of the route to Request object.
     *
     * @param Request $request
     * @return \Magento\Webapi\Controller\Rest\Router\Route
     * @throws \Magento\Webapi\Exception
     */
    public function match(Request $request)
    {
        /** @var \Magento\Webapi\Controller\Rest\Router\Route[] $routes */
        $routes = $this->_apiConfig->getRestRoutes($request);
        $matched = [];
        foreach ($routes as $route) {
            $params = $route->match($request);
            if ($params !== false) {
                $request->setParams($params);
                $matched[] = $route;
            }
        }
        if (!empty($matched)) {
            return array_pop($matched);
        }
        throw new \Magento\Webapi\Exception(
            __('Request does not match any route.'),
            0,
            \Magento\Webapi\Exception::HTTP_NOT_FOUND
        );
    }
}
