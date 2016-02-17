<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router;

use Zend\Stdlib\RequestInterface as Request;

/**
 * RouteInterface interface.
 */
interface RouteInterface
{
    /**
     * Priority used for route stacks.
     *
     * @var int
     * public $priority;
     */

    /**
     * Create a new route with given options.
     *
     * @param  array|\Traversable $options
     * @return void
     */
    public static function factory($options = array());

    /**
     * Match a given request.
     *
     * @param  Request $request
     * @return RouteMatch|null
     */
    public function match(Request $request);

    /**
     * Assemble the route.
     *
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array());
}
