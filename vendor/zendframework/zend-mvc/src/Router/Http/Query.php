<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router\Http;

use Traversable;
use Zend\Mvc\Router\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Query route.
 *
 * @deprecated
 */
class Query implements RouteInterface
{
    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = array();

    /**
     * Create a new wildcard route.
     *
     * @param array $defaults
     */
    public function __construct(array $defaults = array())
    {
        /**
         * Legacy purposes only, to prevent code that uses it from breaking.
         */
        trigger_error('Query route deprecated as of ZF 2.1.4; use the "query" option of the HTTP router\'s assembling method instead', E_USER_DEPRECATED);
        $this->defaults = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Query
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::match()
     * @param  Request $request
     * @return RouteMatch
     */
    public function match(Request $request)
    {
        // We don't merge the query parameters into the rotue match here because
        // of possible security problems. Use the Query object instead which is
        // included in the Request object.
        return new RouteMatch($this->defaults);
    }

    /**
     * Recursively urldecodes keys and values from an array
     *
     * @param  array $array
     * @return array
     */
    protected function recursiveUrldecode(array $array)
    {
        $matches = array();

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $matches[urldecode($key)] = $this->recursiveUrldecode($value);
            } else {
                $matches[urldecode($key)] = urldecode($value);
            }
        }

        return $matches;
    }

    /**
     * assemble(): Defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $mergedParams          = array_merge($this->defaults, $params);
        $this->assembledParams = array();

        if (isset($options['uri']) && count($mergedParams)) {
            foreach ($mergedParams as $key => $value) {
                $this->assembledParams[] = $key;
            }

            $options['uri']->setQuery($mergedParams);
        }

        // A query does not contribute to the path, thus nothing is returned.
        return '';
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return $this->assembledParams;
    }
}
