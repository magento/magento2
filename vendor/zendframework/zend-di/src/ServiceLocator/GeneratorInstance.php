<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Di\ServiceLocator;

/**
 * Container for methods and parameters used by by Di to create a particular instance
 */
class GeneratorInstance
{
    /**
     * @var string|null
     */
    protected $class;

    /**
     * @var string|null
     */
    protected $alias;

    /**
     * @var mixed
     */
    protected $constructor;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $methods = array();

    /**
     * @var bool
     */
    protected $shared = true;

    /**
     * @param string|null $class
     * @param string|null $alias
     * @param mixed       $constructor
     * @param array       $params
     */
    public function __construct($class, $alias, $constructor, array $params)
    {
        $this->class       = $class;
        $this->alias       = $alias;
        $this->constructor = $constructor;
        $this->params      = $params;
    }

    /**
     * Retrieves the best available name for this instance (instance alias first then class name)
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->alias ? $this->alias : $this->class;
    }

    /**
     * Class of the instance. Null if class is unclear (such as when the instance is produced by a callback)
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Alias for the instance (if any)
     *
     * @return string|null
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set class name
     *
     * In the case of an instance created via a callback, we need to set the
     * class name after creating the generator instance.
     *
     * @param  string            $class
     * @return GeneratorInstance
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Set instance alias
     *
     * @param  string            $alias
     * @return GeneratorInstance
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get instantiator
     *
     * @return mixed constructor method name or callable responsible for generating instance
     */
    public function getConstructor()
    {
        return $this->constructor;
    }

    /**
     * Parameters passed to the instantiator as an ordered list of parameters. Each parameter that refers to another
     * instance fetched recursively is a GeneratorInstance itself
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set methods
     *
     * @param  array             $methods
     * @return GeneratorInstance
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Add a method called on the instance
     *
     * @param $method
     * @return GeneratorInstance
     */
    public function addMethod($method)
    {
        $this->methods[] = $method;

        return $this;
    }

    /**
     * Retrieves a list of methods that are called on the instance in their call order. Each returned element has form
     * array('method' => 'methodName', 'params' => array( ... ordered list of call parameters ... ), where every call
     * parameter that is a recursively fetched instance is a GeneratorInstance itself
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param bool $shared
     */
    public function setShared($shared)
    {
        $this->shared = (bool) $shared;
    }

    /**
     * Retrieves whether the instance is shared or not
     *
     * @return bool
     */
    public function isShared()
    {
        return $this->shared;
    }
}
