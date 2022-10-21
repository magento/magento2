<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Code\Generator;

use Magento\Framework\Code\Generator\EntityAbstract;

class Interceptor extends EntityAbstract
{
    public const ENTITY_TYPE = 'interceptor';

    /**
     * Returns default result class name
     *
     * @param string $modelClassName
     * @return string
     */
    protected function _getDefaultResultClassName($modelClassName)
    {
        return $modelClassName . '_' . ucfirst(static::ENTITY_TYPE);
    }

    /**
     * Returns list of properties for class generator
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        return [];
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        $reflectionClass = new \ReflectionClass($this->getSourceClassName());
        $constructor = $reflectionClass->getConstructor();
        $parameters = [];
        $body = "\$this->___init();\n";
        if ($constructor) {
            $parameters = array_map([$this, '_getMethodParameterInfo'], $constructor->getParameters());

            $body .= count($parameters)
                ? "parent::__construct({$this->_getParameterList($parameters)});"
                : "parent::__construct();";
        }

        return [
            'name' => '__construct',
            'parameters' => $parameters,
            'body' => $body
        ];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        $methods = [$this->_getDefaultConstructorDefinition()];

        $reflectionClass = new \ReflectionClass($this->getSourceClassName());
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!$method->isInternal() && $this->isInterceptedMethod($method)) {
                $methods[] = $this->_getMethodInfo($method);
            }
        }

        return $methods;
    }

    /**
     * Whether method is intercepted
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected function isInterceptedMethod(\ReflectionMethod $method)
    {
        return !($method->isConstructor() || $method->isFinal() || $method->isStatic() || $method->isDestructor()) &&
            !in_array($method->getName(), ['__sleep', '__wakeup', '__clone']);
    }

    /**
     * Retrieve method info
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(\ReflectionMethod $method)
    {
        $parameters = array_map([$this, '_getMethodParameterInfo'], $method->getParameters());

        $returnTypeValue = $this->getReturnTypeValue($method);
        $methodInfo = [
            'name' => ($method->returnsReference() ? '& ' : '') . $method->getName(),
            'parameters' => $parameters,
            'body' => str_replace(
                [
                    '%method%',
                    '%return%',
                    '%parameters%'
                ],
                [
                    $method->getName(),
                    $returnTypeValue === 'void' ? '' : 'return ',
                    $this->_getParameterList($parameters)
                ],
                <<<'METHOD_BODY'
$pluginInfo = $this->pluginList->getNext($this->subjectType, '%method%');
%return%$pluginInfo ? $this->___callPlugins('%method%', func_get_args(), $pluginInfo) : parent::%method%(%parameters%);
METHOD_BODY
            ),
                'returnType' => $returnTypeValue,
                'docblock' => ['shortDescription' => '{@inheritdoc}'],
        ];

        return $methodInfo;
    }

    /**
     * Return parameters list
     *
     * @param array $parameters
     * @return string
     */
    protected function _getParameterList(array $parameters)
    {
        return implode(
            ', ',
            array_map(
                function ($item) {
                    $output = '';
                    if (!empty($item['variadic'])) {
                        $output .= '... ';
                    }

                    $output .= "\${$item['name']}";
                    return $output;
                },
                $parameters
            )
        );
    }

    /**
     * Generate resulting class source code
     *
     * @return string
     */
    protected function _generateCode()
    {
        $typeName = $this->getSourceClassName();
        $reflection = new \ReflectionClass($typeName);

        $interfaces = [];
        if ($reflection->isInterface()) {
            $interfaces[] = $typeName;
        } else {
            $this->_classGenerator->setExtendedClass($typeName);
        }
        $this->_classGenerator->addTrait('\\' . \Magento\Framework\Interception\Interceptor::class);
        $interfaces[] = '\\' . \Magento\Framework\Interception\InterceptorInterface::class;
        $this->_classGenerator->setImplementedInterfaces($interfaces);

        return parent::_generateCode();
    }

    /**
     * Validates data
     *
     * @return bool
     */
    protected function _validateData()
    {
        $result = parent::_validateData();

        if ($result) {
            $sourceClassName = $this->getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . '\\Interceptor') {
                $this->_addError(
                    'Invalid Interceptor class name ' . $resultClassName . '. Use ' . $sourceClassName . '\\Interceptor'
                );
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Returns return type
     *
     * @param \ReflectionMethod $method
     * @return null|string
     */
    private function getReturnTypeValue(\ReflectionMethod $method): ?string
    {
        $returnTypeValue = null;
        $returnType = $method->getReturnType();
        if ($returnType) {
            $returnTypeValue = ($returnType->allowsNull() && $returnType->getName() !== 'mixed' ? '?' : '');
            $returnTypeValue .= ($returnType->getName() === 'self')
                ? $this->_getFullyQualifiedClassName($method->getDeclaringClass()->getName())
                : $returnType->getName();
        }

        return $returnTypeValue;
    }
}
