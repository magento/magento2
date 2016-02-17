<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Server\Reflection;

/**
 * Method Reflection
 */
class ReflectionMethod extends AbstractFunction
{
    /**
     * Parent class name
     * @var string
     */
    protected $class;

    /**
     * Parent class reflection
     * @var ReflectionClass
     */
    protected $classReflection;

    /**
     * Constructor
     *
     * @param ReflectionClass $class
     * @param \ReflectionMethod $r
     * @param string $namespace
     * @param array $argv
     */
    public function __construct(ReflectionClass $class, \ReflectionMethod $r, $namespace = null, $argv = array())
    {
        $this->classReflection = $class;
        $this->reflection      = $r;

        $classNamespace = $class->getNamespace();

        // Determine namespace
        if (!empty($namespace)) {
            $this->setNamespace($namespace);
        } elseif (!empty($classNamespace)) {
            $this->setNamespace($classNamespace);
        }

        // Determine arguments
        if (is_array($argv)) {
            $this->argv = $argv;
        }

        // If method call, need to store some info on the class
        $this->class = $class->getName();

        // Perform some introspection
        $this->reflect();
    }

    /**
     * Return the reflection for the class that defines this method
     *
     * @return \Zend\Server\Reflection\ReflectionClass
     */
    public function getDeclaringClass()
    {
        return $this->classReflection;
    }

    /**
     * Wakeup from serialization
     *
     * Reflection needs explicit instantiation to work correctly. Re-instantiate
     * reflection object on wakeup.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->classReflection = new ReflectionClass(new \ReflectionClass($this->class), $this->getNamespace(), $this->getInvokeArguments());
        $this->reflection = new \ReflectionMethod($this->classReflection->getName(), $this->getName());
    }
}
