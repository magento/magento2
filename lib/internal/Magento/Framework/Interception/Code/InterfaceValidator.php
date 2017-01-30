<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
            if (!$this->_argumentsReader->isCompatibleType(
                $subject['type'],
                $interceptedType
            ) || $subject['type'] === null
            ) {
                throw new ValidatorException(
                    new Phrase(
                        'Invalid [%1] $%2 type in %3::%4. It must be compatible with %5',
                        [$subject['type'], $subject['name'], $pluginClass, $pluginMethod->getName(), $interceptedType]
                    )
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
                case self::METHOD_AFTER:
                    if (count($pluginMethodParameters) > 1) {
                        throw new ValidatorException(
                            new Phrase(
                                'Invalid method signature. Detected extra parameters in %1::%2',
                                [$pluginClass, $pluginMethod->getName()]
                            )
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
