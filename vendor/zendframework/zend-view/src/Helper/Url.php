<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Traversable;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\View\Exception;

/**
 * Helper for making easy links and getting urls that depend on the routes and router.
 */
class Url extends AbstractHelper
{
    /**
     * RouteStackInterface instance.
     *
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatch.
     */
    protected $routeMatch;

    /**
     * Generates a url given the name of a route.
     *
     * @see    Zend\Mvc\Router\RouteInterface::assemble()
     * @param  string               $name               Name of the route
     * @param  array                $params             Parameters for the link
     * @param  array|Traversable    $options            Options for the route
     * @param  bool                 $reuseMatchedParams Whether to reuse matched parameters
     * @return string Url                         For the link href attribute
     * @throws Exception\RuntimeException         If no RouteStackInterface was provided
     * @throws Exception\RuntimeException         If no RouteMatch was provided
     * @throws Exception\RuntimeException         If RouteMatch didn't contain a matched route name
     * @throws Exception\InvalidArgumentException If the params object was not an array or \Traversable object
     */
    public function __invoke($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if (null === $this->router) {
            throw new Exception\RuntimeException('No RouteStackInterface instance provided');
        }

        if (3 == func_num_args() && is_bool($options)) {
            $reuseMatchedParams = $options;
            $options = array();
        }

        if ($name === null) {
            if ($this->routeMatch === null) {
                throw new Exception\RuntimeException('No RouteMatch instance provided');
            }

            $name = $this->routeMatch->getMatchedRouteName();

            if ($name === null) {
                throw new Exception\RuntimeException('RouteMatch does not contain a matched route name');
            }
        }

        if (!is_array($params)) {
            if (!$params instanceof Traversable) {
                throw new Exception\InvalidArgumentException(
                    'Params is expected to be an array or a Traversable object'
                );
            }
            $params = iterator_to_array($params);
        }

        if ($reuseMatchedParams && $this->routeMatch !== null) {
            $routeMatchParams = $this->routeMatch->getParams();

            if (isset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
                $routeMatchParams['controller'] = $routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
                unset($routeMatchParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
            }

            if (isset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE])) {
                unset($routeMatchParams[ModuleRouteListener::MODULE_NAMESPACE]);
            }

            $params = array_merge($routeMatchParams, $params);
        }

        $options['name'] = $name;

        return $this->router->assemble($params, $options);
    }

    /**
     * Set the router to use for assembling.
     *
     * @param RouteStackInterface $router
     * @return Url
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Set route match returned by the router.
     *
     * @param  RouteMatch $routeMatch
     * @return Url
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
        return $this;
    }
}
