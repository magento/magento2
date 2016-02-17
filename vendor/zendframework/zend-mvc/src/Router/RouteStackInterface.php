<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router;

interface RouteStackInterface extends RouteInterface
{
    /**
     * Add a route to the stack.
     *
     * @param  string  $name
     * @param  mixed   $route
     * @param  int $priority
     * @return RouteStackInterface
     */
    public function addRoute($name, $route, $priority = null);

    /**
     * Add multiple routes to the stack.
     *
     * @param  array|\Traversable $routes
     * @return RouteStackInterface
     */
    public function addRoutes($routes);

    /**
     * Remove a route from the stack.
     *
     * @param  string $name
     * @return RouteStackInterface
     */
    public function removeRoute($name);

    /**
     * Remove all routes from the stack and set new ones.
     *
     * @param  array|\Traversable $routes
     * @return RouteStackInterface
     */
    public function setRoutes($routes);
}
