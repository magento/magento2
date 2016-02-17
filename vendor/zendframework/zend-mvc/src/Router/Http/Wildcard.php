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
 * Wildcard route.
 */
class Wildcard implements RouteInterface
{
    /**
     * Delimiter between keys and values.
     *
     * @var string
     */
    protected $keyValueDelimiter;

    /**
     * Delimiter before parameters.
     *
     * @var array
     */
    protected $paramDelimiter;

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
     * @param  string $keyValueDelimiter
     * @param  string $paramDelimiter
     * @param  array  $defaults
     */
    public function __construct($keyValueDelimiter = '/', $paramDelimiter = '/', array $defaults = array())
    {
        $this->keyValueDelimiter = $keyValueDelimiter;
        $this->paramDelimiter    = $paramDelimiter;
        $this->defaults          = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Wildcard
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable set of options',
                __METHOD__
            ));
        }

        if (!isset($options['key_value_delimiter'])) {
            $options['key_value_delimiter'] = '/';
        }

        if (!isset($options['param_delimiter'])) {
            $options['param_delimiter'] = '/';
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['key_value_delimiter'], $options['param_delimiter'], $options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::match()
     * @param  Request      $request
     * @param  integer|null $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath() ?: '';

        if ($path === '/') {
            $path = '';
        }

        if ($pathOffset !== null) {
            $path = substr($path, $pathOffset) ?: '';
        }

        $matches = array();
        $params  = explode($this->paramDelimiter, $path);

        if (count($params) > 1 && ($params[0] !== '' || end($params) === '')) {
            return;
        }

        if ($this->keyValueDelimiter === $this->paramDelimiter) {
            $count = count($params);

            for ($i = 1; $i < $count; $i += 2) {
                if (isset($params[$i + 1])) {
                    $matches[rawurldecode($params[$i])] = rawurldecode($params[$i + 1]);
                }
            }
        } else {
            array_shift($params);

            foreach ($params as $param) {
                $param = explode($this->keyValueDelimiter, $param, 2);

                if (isset($param[1])) {
                    $matches[rawurldecode($param[0])] = rawurldecode($param[1]);
                }
            }
        }

        return new RouteMatch(array_merge($this->defaults, $matches), strlen($path));
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
        $elements              = array();
        $mergedParams          = array_merge($this->defaults, $params);
        $this->assembledParams = array();

        if ($mergedParams) {
            foreach ($mergedParams as $key => $value) {
                $elements[] = rawurlencode($key) . $this->keyValueDelimiter . rawurlencode($value);

                $this->assembledParams[] = $key;
            }

            return $this->paramDelimiter . implode($this->paramDelimiter, $elements);
        }

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
