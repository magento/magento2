<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router;

/**
 * RouteInterface match.
 */
class RouteMatch
{
    /**
     * Match parameters.
     *
     * @var array
     */
    protected $params = array();

    /**
     * Matched route name.
     *
     * @var string
     */
    protected $matchedRouteName;

    /**
     * Create a RouteMatch with given parameters.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Set name of matched route.
     *
     * @param  string $name
     * @return RouteMatch
     */
    public function setMatchedRouteName($name)
    {
        $this->matchedRouteName = $name;
        return $this;
    }

    /**
     * Get name of matched route.
     *
     * @return string
     */
    public function getMatchedRouteName()
    {
        return $this->matchedRouteName;
    }

    /**
     * Set a parameter.
     *
     * @param  string $name
     * @param  mixed  $value
     * @return RouteMatch
     */
    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Get all parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get a specific parameter.
     *
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return $default;
    }
}
