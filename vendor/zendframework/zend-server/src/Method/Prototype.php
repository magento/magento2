<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Server\Method;

/**
 * Method prototype metadata
 */
class Prototype
{
    /**
     * @var string Return type
     */
    protected $returnType = 'void';

    /**
     * @var array Map parameter names to parameter index
     */
    protected $parameterNameMap = array();

    /**
     * @var array Method parameters
     */
    protected $parameters = array();

    /**
     * Constructor
     *
     * @param  null|array $options
     */
    public function __construct($options = null)
    {
        if ((null !== $options) && is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set return value
     *
     * @param  string $returnType
     * @return \Zend\Server\Method\Prototype
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * Retrieve return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Add a parameter
     *
     * @param  string $parameter
     * @return \Zend\Server\Method\Prototype
     */
    public function addParameter($parameter)
    {
        if ($parameter instanceof Parameter) {
            $this->parameters[] = $parameter;
            if (null !== ($name = $parameter->getName())) {
                $this->parameterNameMap[$name] = count($this->parameters) - 1;
            }
        } else {
            $parameter = new Parameter(array(
                'type' => (string) $parameter,
            ));
            $this->parameters[] = $parameter;
        }
        return $this;
    }

    /**
     * Add parameters
     *
     * @param  array $parameters
     * @return \Zend\Server\Method\Prototype
     */
    public function addParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }
        return $this;
    }

    /**
     * Set parameters
     *
     * @param  array $parameters
     * @return \Zend\Server\Method\Prototype
     */
    public function setParameters(array $parameters)
    {
        $this->parameters       = array();
        $this->parameterNameMap = array();
        $this->addParameters($parameters);
        return $this;
    }

    /**
     * Retrieve parameters as list of types
     *
     * @return array
     */
    public function getParameters()
    {
        $types = array();
        foreach ($this->parameters as $parameter) {
            $types[] = $parameter->getType();
        }
        return $types;
    }

    /**
     * Get parameter objects
     *
     * @return array
     */
    public function getParameterObjects()
    {
        return $this->parameters;
    }

    /**
     * Retrieve a single parameter by name or index
     *
     * @param  string|int $index
     * @return null|\Zend\Server\Method\Parameter
     */
    public function getParameter($index)
    {
        if (!is_string($index) && !is_numeric($index)) {
            return;
        }
        if (array_key_exists($index, $this->parameterNameMap)) {
            $index = $this->parameterNameMap[$index];
        }
        if (array_key_exists($index, $this->parameters)) {
            return $this->parameters[$index];
        }
        return;
    }

    /**
     * Set object state from array
     *
     * @param  array $options
     * @return \Zend\Server\Method\Prototype
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Serialize to array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'returnType' => $this->getReturnType(),
            'parameters' => $this->getParameters(),
        );
    }
}
