<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Resolver;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Exception;
use Zend\View\Renderer\RendererInterface as Renderer;

class TemplateMapResolver implements IteratorAggregate, ResolverInterface
{
    /**
     * @var array
     */
    protected $map = array();

    /**
     * Constructor
     *
     * Instantiate and optionally populate template map.
     *
     * @param  array|Traversable $map
     */
    public function __construct($map = array())
    {
        $this->setMap($map);
    }

    /**
     * IteratorAggregate: return internal iterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->map);
    }

    /**
     * Set (overwrite) template map
     *
     * Maps should be arrays or Traversable objects with name => path pairs
     *
     * @param  array|Traversable $map
     * @throws Exception\InvalidArgumentException
     * @return TemplateMapResolver
     */
    public function setMap($map)
    {
        if (!is_array($map) && !$map instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable, received "%s"',
                __METHOD__,
                (is_object($map) ? get_class($map) : gettype($map))
            ));
        }

        if ($map instanceof Traversable) {
            $map = ArrayUtils::iteratorToArray($map);
        }

        $this->map = $map;
        return $this;
    }

    /**
     * Add an entry to the map
     *
     * @param  string|array|Traversable $nameOrMap
     * @param  null|string $path
     * @throws Exception\InvalidArgumentException
     * @return TemplateMapResolver
     */
    public function add($nameOrMap, $path = null)
    {
        if (is_array($nameOrMap) || $nameOrMap instanceof Traversable) {
            $this->merge($nameOrMap);
            return $this;
        }

        if (!is_string($nameOrMap)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects a string, array, or Traversable for the first argument; received "%s"',
                __METHOD__,
                (is_object($nameOrMap) ? get_class($nameOrMap) : gettype($nameOrMap))
            ));
        }

        if (empty($path)) {
            if (isset($this->map[$nameOrMap])) {
                unset($this->map[$nameOrMap]);
            }
            return $this;
        }

        $this->map[$nameOrMap] = $path;
        return $this;
    }

    /**
     * Merge internal map with provided map
     *
     * @param  array|Traversable $map
     * @throws Exception\InvalidArgumentException
     * @return TemplateMapResolver
     */
    public function merge($map)
    {
        if (!is_array($map) && !$map instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable, received "%s"',
                __METHOD__,
                (is_object($map) ? get_class($map) : gettype($map))
            ));
        }

        if ($map instanceof Traversable) {
            $map = ArrayUtils::iteratorToArray($map);
        }

        $this->map = array_replace_recursive($this->map, $map);
        return $this;
    }

    /**
     * Does the resolver contain an entry for the given name?
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->map);
    }

    /**
     * Retrieve a template path by name
     *
     * @param  string $name
     * @return false|string
     * @throws Exception\DomainException if no entry exists
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            return false;
        }
        return $this->map[$name];
    }

    /**
     * Retrieve the template map
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Resolve a template/pattern name to a resource the renderer can consume
     *
     * @param  string $name
     * @param  null|Renderer $renderer
     * @return string
     */
    public function resolve($name, Renderer $renderer = null)
    {
        return $this->get($name);
    }
}
