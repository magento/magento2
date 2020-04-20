<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code;

use Magento\Framework\Code\Reader\ArgumentsReader;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;

class InterfaceValidator
{
    public const METHOD_BEFORE = 'before';
    public const METHOD_AROUND = 'around';
    public const METHOD_AFTER = 'after';

    /**
     * Arguments reader model
     *
     * @var ArgumentsReader
     */
    protected $_argumentsReader;

    /**
     * @param ArgumentsReader $argumentsReader
     */
    public function __construct(ArgumentsReader $argumentsReader = null)
    {
        $this->_argumentsReader = $argumentsReader ?? new ArgumentsReader();
    }

    /**
     * Validate plugin interface
     *
     * @param string $pluginClass
     * @param string $interceptedType
     *
     * @return void
     * @throws ValidatorException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function validate($pluginClass, $interceptedType)
    {
        $interceptedType = '\\' . trim($interceptedType, '\\');
        $pluginClass = '\\' . trim($pluginClass, '\\');
        $plugin = new \ReflectionClass($pluginClass);
        $type = new \ReflectionClass($interceptedType);

        foreach ($plugin->getMethods(\ReflectionMethod::IS_PUBLIC) as $pluginMethod) {
            /** @var \ReflectionMethod $pluginMethod */
            $originMethodName = $this->getOriginMethodName($pluginMethod->getName());
            if ($originMethodName === null) {
                continue;
            }
            if (!$type->hasMethod($originMethodName)) {
                throw new ValidatorException(
                    new Phrase(
                        'Incorrect interface in %1. There is no method [ %2 ] in %3 interface',
                        [$pluginClass, $originMethodName, $interceptedType]
                    )
                );
            }
            $originMethod = $type->getMethod($originMethodName);

            $pluginMethodParameters = $this->getMethodParameters($pluginMethod);
            $originMethodParameters = $this->getMethodParameters($originMethod);

            $methodType = $this->getMethodType($pluginMethod->getName());

            $subject = array_shift($pluginMethodParameters);
            if ($subject['type'] === null
                || !$this->_argumentsReader->isCompatibleType($subject['type'], $interceptedType)) {
                throw new ValidatorException(
                    new Phrase(
                        'Invalid [%1] $%2 type in %3::%4. It must be compatible with %5',
                        [$subject['type'], $subject['name'], $pluginClass, $pluginMethod->getName(), $interceptedType]
                    )
                );
            }

            switch ($methodType) {
                case self::METHOD_AFTER:
                    if (count($pluginMethodParameters) > 1) {
                        // remove result
                        array_shift($pluginMethodParameters);
                        $matchedParameters = array_intersect_key($originMethodParameters, $pluginMethodParameters);
                        $this->validateMethodsParameters(
                            $pluginMethodParameters,
                            $matchedParameters,
                            $pluginClass,
                            $pluginMethod->getName()
                        );
                    }
                    break;
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
                            new Phrase(
                                'Invalid [%1] $%2 type in %3::%4. It must be compatible with \\Closure',
                                [$proceed['type'], $proceed['name'], $pluginClass, $pluginMethod->getName()]
                            )
                        );
                    }
                    $this->validateMethodsParameters(
                        $pluginMethodParameters,
                        $originMethodParameters,
                        $pluginClass,
                        $pluginMethod->getName()
                    );
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
                new Phrase(
                    'Invalid method signature. Invalid method parameters count in %1::%2',
                    [$class, $method]
                )
            );
        }
        foreach ($pluginParameters as $position => $data) {
            if (!$this->_argumentsReader->isCompatibleType($data['type'], $originParameters[$position]['type'])) {
                throw new ValidatorException(
                    new Phrase(
                        'Incompatible parameter type [%1 $%2] in %3::%4. It must be compatible with %5',
                        [$data['type'], $data['name'], $class, $method, $originParameters[$position]['type']]
                    )
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
            case self::METHOD_AFTER:
                return lcfirst(substr($pluginMethodName, 5));

            case self::METHOD_BEFORE:
            case self::METHOD_AROUND:
                return lcfirst(substr($pluginMethodName, 6));

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
        if (0 === strpos($pluginMethodName, self::METHOD_AFTER)) {
            return self::METHOD_AFTER;
        }
        if (0 === strpos($pluginMethodName, self::METHOD_BEFORE)) {
            return self::METHOD_BEFORE;
        }
        if (0 === strpos($pluginMethodName, self::METHOD_AROUND)) {
            return self::METHOD_AROUND;
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
