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
 * Regex route.
 */
class Regex implements RouteInterface
{
    /**
     * Regex to match.
     *
     * @var string
     */
    protected $regex;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Specification for URL assembly.
     *
     * Parameters accepting substitutions should be denoted as "%key%"
     *
     * @var string
     */
    protected $spec;

    /**
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = array();

    /**
     * Create a new regex route.
     *
     * @param  string $regex
     * @param  string $spec
     * @param  array  $defaults
     */
    public function __construct($regex, $spec, array $defaults = array())
    {
        $this->regex    = $regex;
        $this->spec     = $spec;
        $this->defaults = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Regex
     * @throws \Zend\Mvc\Router\Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['regex'])) {
            throw new Exception\InvalidArgumentException('Missing "regex" in options array');
        }

        if (!isset($options['spec'])) {
            throw new Exception\InvalidArgumentException('Missing "spec" in options array');
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['regex'], $options['spec'], $options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @param  Request $request
     * @param  int $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset !== null) {
            $result = preg_match('(\G' . $this->regex . ')', $path, $matches, null, $pathOffset);
        } else {
            $result = preg_match('(^' . $this->regex . '$)', $path, $matches);
        }

        if (!$result) {
            return;
        }

        $matchedLength = strlen($matches[0]);

        foreach ($matches as $key => $value) {
            if (is_numeric($key) || is_int($key) || $value === '') {
                unset($matches[$key]);
            } else {
                $matches[$key] = rawurldecode($value);
            }
        }

        return new RouteMatch(array_merge($this->defaults, $matches), $matchedLength);
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
        $url                   = $this->spec;
        $mergedParams          = array_merge($this->defaults, $params);
        $this->assembledParams = array();

        foreach ($mergedParams as $key => $value) {
            $spec = '%' . $key . '%';

            if (strpos($url, $spec) !== false) {
                $url = str_replace($spec, rawurlencode($value), $url);

                $this->assembledParams[] = $key;
            }
        }

        return $url;
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
