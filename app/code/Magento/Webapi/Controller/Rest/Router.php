<?php
/**
 * Router for Magento web API.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Exception;
use \Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Rest\Config;

class Router
{
    /**
     * @var array
     */
    protected $_routes = [];

    /**
     * @var Config
     */
    protected $_apiConfig;

    /**
     * Initialize dependencies.
     *
     * @param Config $apiConfig
     */
    public function __construct(Config $apiConfig)
    {
        $this->_apiConfig = $apiConfig;
    }

    /**
     * Route the Request, the only responsibility of the class.
     * Find route that matches current URL, set parameters of the route to Request object.
     *
     * @param Request $request
     * @return Route
     * @throws Exception
     */
    public function match(Request $request)
    {
        /** @var Route[] $routes */
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
        throw new Exception(
            __('Request does not match any route.'),
            0,
            Exception::HTTP_NOT_FOUND
        );
    }
}
