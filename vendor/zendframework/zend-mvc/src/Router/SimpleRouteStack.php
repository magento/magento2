<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Simple route stack implementation.
 */
class SimpleRouteStack implements RouteStackInterface
{
    /**
     * Stack containing all routes.
     *
     * @var PriorityList
     */
    protected $routes;

    /**
     * Route plugin manager
     *
     * @var RoutePluginManager
     */
    protected $routePluginManager;

    /**
     * Default parameters.
     *
     * @var array
     */
    protected $defaultParams = array();

    /**
     * Create a new simple route stack.
     *
     * @param RoutePluginManager $routePluginManager
     */
    public function __construct(RoutePluginManager $routePluginManager = null)
    {
        $this->routes = new PriorityList();

        if (null === $routePluginManager) {
            $routePluginManager = new RoutePluginManager();
        }

        $this->routePluginManager = $routePluginManager;

        $this->init();
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return SimpleRouteStack
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        $routePluginManager = null;
        if (isset($options['route_plugins'])) {
            $routePluginManager = $options['route_plugins'];
        }

        $instance = new static($routePluginManager);

        if (isset($options['routes'])) {
            $instance->addRoutes($options['routes']);
        }

        if (isset($options['default_params'])) {
            $instance->setDefaultParams($options['default_params']);
        }

        return $instance;
    }

    /**
     * Init method for extending classes.
     *
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Set the route plugin manager.
     *
     * @param  RoutePluginManager $routePlugins
     * @return SimpleRouteStack
     */
    public function setRoutePluginManager(RoutePluginManager $routePlugins)
    {
        $this->routePluginManager = $routePlugins;
        return $this;
    }

    /**
     * Get the route plugin manager.
     *
     * @return RoutePluginManager
     */
    public function getRoutePluginManager()
    {
        return $this->routePluginManager;
    }

    /**
     * addRoutes(): defined by RouteStackInterface interface.
     *
     * @see    RouteStackInterface::addRoutes()
     * @param  array|Traversable $routes
     * @return SimpleRouteStack
     * @throws Exception\InvalidArgumentException
     */
    public function addRoutes($routes)
    {
        if (!is_array($routes) && !$routes instanceof Traversable) {
            throw new Exception\InvalidArgumentException('addRoutes expects an array or Traversable set of routes');
        }

        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }

        return $this;
    }

    /**
     * addRoute(): defined by RouteStackInterface interface.
     *
     * @see    RouteStackInterface::addRoute()
     * @param  string  $name
     * @param  mixed   $route
     * @param  int $priority
     * @return SimpleRouteStack
     */
    public function addRoute($name, $route, $priority = null)
    {
        if (!$route instanceof RouteInterface) {
            $route = $this->routeFromArray($route);
        }

        if ($priority === null && isset($route->priority)) {
            $priority = $route->priority;
        }

        $this->routes->insert($name, $route, $priority);

        return $this;
    }

    /**
     * removeRoute(): defined by RouteStackInterface interface.
     *
     * @see    RouteStackInterface::removeRoute()
     * @param  string $name
     * @return SimpleRouteStack
     */
    public function removeRoute($name)
    {
        $this->routes->remove($name);
        return $this;
    }

    /**
     * setRoutes(): defined by RouteStackInterface interface.
     *
     * @param  array|Traversable $routes
     * @return SimpleRouteStack
     */
    public function setRoutes($routes)
    {
        $this->routes->clear();
        $this->addRoutes($routes);
        return $this;
    }

    /**
     * Get the added routes
     *
     * @return Traversable list of all routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Check if a route with a specific name exists
     *
     * @param  string $name
     * @return bool true if route exists
     */
    public function hasRoute($name)
    {
        return $this->routes->get($name) !== null;
    }

    /**
     * Get a route by name
     *
     * @param string $name
     * @return RouteInterface the route
     */
    public function getRoute($name)
    {
        return $this->routes->get($name);
    }

    /**
     * Set a default parameters.
     *
     * @param  array $params
     * @return SimpleRouteStack
     */
    public function setDefaultParams(array $params)
    {
        $this->defaultParams = $params;
        return $this;
    }

    /**
     * Set a default parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return SimpleRouteStack
     */
    public function setDefaultParam($name, $value)
    {
        $this->defaultParams[$name] = $value;
        return $this;
    }

    /**
     * Create a route from array specifications.
     *
     * @param  array|Traversable $specs
     * @return RouteInterface
     * @throws Exception\InvalidArgumentException
     */
    protected function routeFromArray($specs)
    {
        if ($specs instanceof Traversable) {
            $specs = ArrayUtils::iteratorToArray($specs);
        } elseif (!is_array($specs)) {
            throw new Exception\InvalidArgumentException('Route definition must be an array or Traversable object');
        }

        if (!isset($specs['type'])) {
            throw new Exception\InvalidArgumentException('Missing "type" option');
        } elseif (!isset($specs['options'])) {
            $specs['options'] = array();
        }

        $route = $this->getRoutePluginManager()->get($specs['type'], $specs['options']);

        if (isset($specs['priority'])) {
            $route->priority = $specs['priority'];
        }

        return $route;
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::match()
     * @param  Request $request
     * @return RouteMatch|null
     */
    public function match(Request $request)
    {
        foreach ($this->routes as $name => $route) {
            if (($match = $route->match($request)) instanceof RouteMatch) {
                $match->setMatchedRouteName($name);

                foreach ($this->defaultParams as $paramName => $value) {
                    if ($match->getParam($paramName) === null) {
                        $match->setParam($paramName, $value);
                    }
                }

                return $match;
            }
        }

        return;
    }

    /**
     * assemble(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function assemble(array $params = array(), array $options = array())
    {
        if (!isset($options['name'])) {
            throw new Exception\InvalidArgumentException('Missing "name" option');
        }

        $route = $this->routes->get($options['name']);

        if (!$route) {
            throw new Exception\RuntimeException(sprintf('Route with name "%s" not found', $options['name']));
        }

        unset($options['name']);

        return $route->assemble(array_merge($this->defaultParams, $params), $options);
    }
}
