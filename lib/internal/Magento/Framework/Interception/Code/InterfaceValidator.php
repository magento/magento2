<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code;

use Magento\Framework\Code\Reader\ArgumentsReader;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\GetParameterClassTrait;
use Magento\Framework\Phrase;
use ReflectionParameter;

/**
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class InterfaceValidator
{
    use GetParameterClassTrait;

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
     * List of optional packages
     *
     * @var array
     */
    public static array $optionalPackages = [
        'Swoole',
        'OpenSwoole'
    ];

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
        // check if $interceptedType is a part of optional package
        $interceptedPackage = strstr(trim((string)$interceptedType), "\\", true);
        if (in_array($interceptedPackage, self::$optionalPackages)) {
            return;
        }

        $interceptedType = '\\' . trim((string)$interceptedType, '\\');
        $pluginClass = '\\' . trim((string)$pluginClass, '\\');
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

            $pluginMethodParameters = $this->getMethodParameters($pluginMethod);
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

            $originMethod = $type->getMethod($originMethodName);
            $originMethodParameters = $this->getMethodParameters($originMethod);
            $methodType = $this->getMethodType($pluginMethod->getName());

            if (self::METHOD_AFTER === $methodType && count($pluginMethodParameters) > 1) {
                // remove result
                array_shift($pluginMethodParameters);
                $matchedParameters = array_intersect_key($originMethodParameters, $pluginMethodParameters);
                $this->validateMethodsParameters(
                    $pluginMethodParameters,
                    $matchedParameters,
                    $pluginClass,
                    $pluginMethod->getName()
                );
                continue;
            }

            if (self::METHOD_BEFORE === $methodType) {
                $this->validateMethodsParameters(
                    $pluginMethodParameters,
                    $originMethodParameters,
                    $pluginClass,
                    $pluginMethod->getName()
                );
                continue;
            }

            if (self::METHOD_AROUND === $methodType) {
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
                continue;
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
     * @param ReflectionParameter $parameter
     *
     * @return string
     */
    protected function getParametersType(ReflectionParameter $parameter)
    {
        $parameterClass = $this->getParameterClass($parameter);
        $parameterType = $parameter->getType();
        return $parameterClass ?
            '\\' . $parameterClass->getName() :
            ($parameterType && $parameterType->getName() === 'array' ? 'array' : null);
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
        $methodType = $this->getMethodType($pluginMethodName);

        if (self::METHOD_AFTER === $methodType) {
            return lcfirst(substr($pluginMethodName, 5));
        }
        if (self::METHOD_BEFORE === $methodType || self::METHOD_AROUND === $methodType) {
            return lcfirst(substr($pluginMethodName, 6));
        }

        return null;
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
