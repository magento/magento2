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
 * Scheme route.
 */
class Scheme implements RouteInterface
{
    /**
     * Scheme to match.
     *
     * @var array
     */
    protected $scheme;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Create a new scheme route.
     *
     * @param  string $scheme
     * @param  array  $defaults
     */
    public function __construct($scheme, array $defaults = array())
    {
        $this->scheme   = $scheme;
        $this->defaults = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Scheme
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['scheme'])) {
            throw new Exception\InvalidArgumentException('Missing "scheme" in options array');
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['scheme'], $options['defaults']);
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
        if (!method_exists($request, 'getUri')) {
            return;
        }

        $uri    = $request->getUri();
        $scheme = $uri->getScheme();

        if ($scheme !== $this->scheme) {
            return;
        }

        return new RouteMatch($this->defaults);
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
        if (isset($options['uri'])) {
            $options['uri']->setScheme($this->scheme);
        }

        // A scheme does not contribute to the path, thus nothing is returned.
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
        return array();
    }
}
