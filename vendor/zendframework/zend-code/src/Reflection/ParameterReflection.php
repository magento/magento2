<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection;

use ReflectionParameter;

class ParameterReflection extends ReflectionParameter implements ReflectionInterface
{
    /**
     * @var bool
     */
    protected $isFromMethod = false;

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    public function getDeclaringClass()
    {
        $phpReflection  = parent::getDeclaringClass();
        $zendReflection = new ClassReflection($phpReflection->getName());
        unset($phpReflection);

        return $zendReflection;
    }

    /**
     * Get class reflection object
     *
     * @return ClassReflection
     */
    public function getClass()
    {
        $phpReflection = parent::getClass();
        if ($phpReflection === null) {
            return;
        }

        $zendReflection = new ClassReflection($phpReflection->getName());
        unset($phpReflection);

        return $zendReflection;
    }

    /**
     * Get declaring function reflection object
     *
     * @return FunctionReflection|MethodReflection
     */
    public function getDeclaringFunction()
    {
        $phpReflection = parent::getDeclaringFunction();
        if ($phpReflection instanceof \ReflectionMethod) {
            $zendReflection = new MethodReflection($this->getDeclaringClass()->getName(), $phpReflection->getName());
        } else {
            $zendReflection = new FunctionReflection($phpReflection->getName());
        }
        unset($phpReflection);

        return $zendReflection;
    }

    /**
     * Get parameter type
     *
     * @return string
     */
    public function getType()
    {
        if ($this->isArray()) {
            return 'array';
        } elseif (method_exists($this, 'isCallable') && $this->isCallable()) {
            return 'callable';
        }

        if (($class = $this->getClass()) instanceof \ReflectionClass) {
            return $class->getName();
        }

        $docBlock = $this->getDeclaringFunction()->getDocBlock();
        if (!$docBlock instanceof DocBlockReflection) {
            return;
        }

        $params = $docBlock->getTags('param');
        if (isset($params[$this->getPosition()])) {
            return $params[$this->getPosition()]->getType();
        }

        return;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return parent::__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }
}
