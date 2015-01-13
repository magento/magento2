<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code;

class InterfaceValidator
{
    const METHOD_BEFORE = 'before';

    const METHOD_AROUND = 'around';

    const METHOD_AFTER = 'after';

    /**
     * Arguments reader model
     *
     * @var \Magento\Framework\Code\Reader\ArgumentsReader
     */
    protected $_argumentsReader;

    /**
     * @param \Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader
     */
    public function __construct(\Magento\Framework\Code\Reader\ArgumentsReader $argumentsReader = null)
    {
        $this->_argumentsReader = $argumentsReader ?: new \Magento\Framework\Code\Reader\ArgumentsReader();
    }

    /**
     * Validate plugin interface
     *
     * @param string $pluginClass
     * @param string $interceptedType
     *
     * @return void
     * @throws ValidatorException
     */
    public function validate($pluginClass, $interceptedType)
    {
        $interceptedType = '\\' . trim($interceptedType, '\\');
        $pluginClass = '\\' . trim($pluginClass, '\\');
        $plugin = new \ReflectionClass($pluginClass);
        $type = new \ReflectionClass($interceptedType);

        $pluginMethods = [];
        foreach ($plugin->getMethods(\ReflectionMethod::IS_PUBLIC) as $pluginMethod) {
            /** @var  $pluginMethod \ReflectionMethod */
            $originMethodName = $this->getOriginMethodName($pluginMethod->getName());
            if (is_null($originMethodName)) {
                continue;
            }
            if (!$type->hasMethod($originMethodName)) {
                throw new ValidatorException(
                    'Incorrect interface in ' .
                    $pluginClass .
                    '. There is no method [ ' .
                    $originMethodName .
                    ' ] in ' .
                    $interceptedType .
                    ' interface'
                );
            }
            $originMethod = $type->getMethod($originMethodName);

            $pluginMethodParameters = $this->getMethodParameters($pluginMethod);
            $originMethodParameters = $this->getMethodParameters($originMethod);

            $methodType = $this->getMethodType($pluginMethod->getName());

            $subject = array_shift($pluginMethodParameters);
            if (!$this->_argumentsReader->isCompatibleType(
                $subject['type'],
                $interceptedType
            ) || is_null(
                $subject['type']
            )
            ) {
                throw new ValidatorException(
                    'Invalid [' .
                    $subject['type'] .
                    '] $' .
                    $subject['name'] .
                    ' type in ' .
                    $pluginClass .
                    '::' .
                    $pluginMethod->getName() .
                    '. It must be compatible with ' .
                    $interceptedType
                );
            }

            switch ($methodType) {
                case self::METHOD_BEFORE:
                    $this->validateMethodsParameters(
                        $pluginMethodParameters,
                        $originMethodParameters,
                        $pluginClass,
                        $pluginMethod->getName()
                    );
                    break;
                case self::METHOD_AROUND:
                    $proceed = array_shift($pluginMethodParameters);
                    if (!$this->_argumentsReader->isCompatibleType($proceed['type'], '\\Closure')) {
                        throw new ValidatorException(
                            'Invalid [' .
                            $proceed['type'] .
                            '] $' .
                            $proceed['name'] .
                            ' type in ' .
                            $pluginClass .
                            '::' .
                            $pluginMethod->getName() .
                            '. It must be compatible with \\Closure'
                        );
                    }
                    $this->validateMethodsParameters(
                        $pluginMethodParameters,
                        $originMethodParameters,
                        $pluginClass,
                        $pluginMethod->getName()
                    );
                    break;
                case self::METHOD_AFTER:
                    if (count($pluginMethodParameters) > 1) {
                        throw new ValidatorException(
                            'Invalid method signature. Detected extra parameters' .
                            ' in ' .
                            $pluginClass .
                            '::' .
                            $pluginMethod->getName()
                        );
                    }
                    break;
            }
        }
    }

    /**
     * Validate methods parameters compatibility
     *
     * @param array $pluginParameters
     * @param array $originParameters
     * @param string $class
     * @param string $method
     *
     * @return void
     * @throws ValidatorException
     */
    protected function validateMethodsParameters(array $pluginParameters, array $originParameters, $class, $method)
    {
        if (count($pluginParameters) != count($originParameters)) {
            throw new ValidatorException(
                'Invalid method signature. Invalid method parameters count' . ' in ' . $class . '::' . $method
            );
        }
        foreach ($pluginParameters as $position => $data) {
            if (!$this->_argumentsReader->isCompatibleType($data['type'], $originParameters[$position]['type'])) {
                throw new ValidatorException(
                    'Incompatible parameter type [' .
                    $data['type'] .
                    ' $' .
                    $data['name'] .
                    ']' .
                    ' in ' .
                    $class .
                    '::' .
                    $method .
                    '. It must be compatible with ' .
                    $originParameters[$position]['type']
                );
            }
        }
    }

    /**
     * Get parameters type
     *
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    protected function getParametersType(\ReflectionParameter $parameter)
    {
        $parameterClass = $parameter->getClass();
        $type = $parameterClass ? '\\' . $parameterClass->getName() : ($parameter->isArray() ? 'array' : null);
        return $type;
    }

    /**
     * Get intercepted method name
     *
     * @param string $pluginMethodName
     *
     * @return string|null
     */
    protected function getOriginMethodName($pluginMethodName)
    {
        switch ($this->getMethodType($pluginMethodName)) {
            case self::METHOD_BEFORE:
            case self::METHOD_AROUND:
                return lcfirst(substr($pluginMethodName, 6));

            case self::METHOD_AFTER:
                return lcfirst(substr($pluginMethodName, 5));

            default:
                return null;
        }
    }

    /**
     * Get method type
     *
     * @param string $pluginMethodName
     *
     * @return null|string
     */
    protected function getMethodType($pluginMethodName)
    {
        if (substr($pluginMethodName, 0, 6) == self::METHOD_BEFORE) {
            return self::METHOD_BEFORE;
        } elseif (substr($pluginMethodName, 0, 6) == self::METHOD_AROUND) {
            return self::METHOD_AROUND;
        } elseif (substr($pluginMethodName, 0, 5) == self::METHOD_AFTER) {
            return self::METHOD_AFTER;
        }

        return null;
    }

    /**
     * Get method parameters
     *
     * @param \ReflectionMethod $method
     *
     * @return array
     */
    protected function getMethodParameters(\ReflectionMethod $method)
    {
        $output = [];
        foreach ($method->getParameters() as $parameter) {
            $output[$parameter->getPosition()] = [
                'name' => $parameter->getName(),
                'type' => $this->getParametersType($parameter),
            ];
        }
        return $output;
    }
}
