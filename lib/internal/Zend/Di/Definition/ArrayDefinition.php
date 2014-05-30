<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Di
 */

namespace Zend\Di\Definition;

/**
 * Class definitions based on a given array
 *
 * @category   Zend
 * @package    Zend_Di
 */
class ArrayDefinition implements DefinitionInterface
{
    /**
     * @var array
     */
    protected $dataArray = array();

    /**
     * @param array $dataArray
     */
    public function __construct(array $dataArray)
    {
        foreach ($dataArray as $class => $value) {
            // force lower names
            $dataArray[$class] = array_change_key_case($dataArray[$class], CASE_LOWER);
        }
        $this->dataArray = $dataArray;
    }

    /**
     * {@inheritDoc}
     */
    public function getClasses()
    {
        return array_keys($this->dataArray);
    }

    /**
     * {@inheritDoc}
     */
    public function hasClass($class)
    {
        return array_key_exists($class, $this->dataArray);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassSupertypes($class)
    {
        if (!isset($this->dataArray[$class])) {
            return array();
        }

        if (!isset($this->dataArray[$class]['supertypes'])) {
            return array();
        }

        return $this->dataArray[$class]['supertypes'];
    }

    /**
     * {@inheritDoc}
     */
    public function getInstantiator($class)
    {
        if (!isset($this->dataArray[$class])) {
            return null;
        }

        if (!isset($this->dataArray[$class]['instantiator'])) {
            return '__construct';
        }

        return $this->dataArray[$class]['instantiator'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethods($class)
    {
        if (!isset($this->dataArray[$class])) {
            return false;
        }

        if (!isset($this->dataArray[$class]['methods'])) {
            return false;
        }

        return (count($this->dataArray[$class]['methods']) > 0);
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethod($class, $method)
    {
        if (!isset($this->dataArray[$class])) {
            return false;
        }

        if (!isset($this->dataArray[$class]['methods'])) {
            return false;
        }

        return array_key_exists($method, $this->dataArray[$class]['methods']);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods($class)
    {
        if (!isset($this->dataArray[$class])) {
            return array();
        }

        if (!isset($this->dataArray[$class]['methods'])) {
            return array();
        }

        return $this->dataArray[$class]['methods'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMethodParameters($class, $method)
    {
        return isset($this->dataArray[$class]['parameters'][$method]);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodParameters($class, $method)
    {
        if (!isset($this->dataArray[$class])) {
            return array();
        }

        if (!isset($this->dataArray[$class]['parameters'])) {
            return array();
        }

        if (!isset($this->dataArray[$class]['parameters'][$method])) {
            return array();
        }

        return $this->dataArray[$class]['parameters'][$method];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->dataArray;
    }

}
