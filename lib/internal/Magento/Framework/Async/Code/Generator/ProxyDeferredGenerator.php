<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Async\Code\Generator;

use Magento\Framework\Async\DeferredInterface;
use Magento\Framework\Code\Generator\EntityAbstract;
use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\ObjectManager\NoninterceptableInterface;

/**
 * Generator for proxies for late values resolving.
 */
class ProxyDeferredGenerator extends EntityAbstract
{
    /**
     * Entity type
     */
    public const ENTITY_TYPE = 'proxyDeferred';

    /**
     * @inheritDoc
     */
    protected function _getDefaultResultClassName($modelClassName)
    {
        return $modelClassName . '_' . ucfirst(static::ENTITY_TYPE);
    }

    /**
     * @inheritDoc
     */
    protected function _getClassProperties()
    {
        $properties[] = [
            'name' => 'instance',
            'visibility' => 'private',
            'docblock' => [
                'shortDescription' => 'Proxied instance',
                'tags' => [['name' => 'var', 'description' => 'string']],
            ],
        ];
        $properties[] = [
            'name' => 'deferred',
            'visibility' => 'private',
            'docblock' => [
                'shortDescription' => 'Deferred to wait for',
                'tags' => [['name' => 'var', 'description' => 'string']],
            ],
        ];

        return $properties;
    }

    /**
     * @inheritDoc
     */
    protected function _getClassMethods()
    {
        $construct = $this->_getDefaultConstructorDefinition();
        $sourceClassName = $this->getSourceClassName();

        // create proxy methods for all non-static and non-final public methods (excluding constructor)
        $methods = [$construct];
        //Only serializing the result.
        $methods[] = [
            'name' => '__sleep',
            'body' => "\$this->wait();\nreturn ['instance'];",
            'docblock' => [
                'shortDescription' => 'Serialize only the instance',
                'tags' => [['name' => 'return', 'description' => 'array']]
            ],
        ];
        //Only cloning the result.
        $methods[] = [
            'name' => '__clone',
            'body' => "\$this->wait();\n\$this->instance = clone \$this->instance;",
            'docblock' => ['shortDescription' => 'Clone proxied instance'],
        ];
        //Getting deferred value.
        $methods[] = [
            'name' => 'wait',
            'visibility' => 'private',
            'body' => "if (!\$this->instance) {\n" .
                "    \$this->instance = \$this->deferred->get();\n" .
                "    if (!\$this->instance instanceof $sourceClassName) {\n" .
                "        throw new \\RuntimeException('Wrong instance returned by deferred');\n" .
                "    }\n" .
                "}\n" .
                "return \$this->instance;",
            'docblock' => [
                'shortDescription' => 'Get proxied instance',
                'tags' => [['name' => 'return', 'description' => $sourceClassName]],
            ],
        ];
        $reflectionClass = new \ReflectionClass($sourceClassName);
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!(
                    $method->isConstructor() ||
                    $method->isFinal() ||
                    $method->isStatic() ||
                    $method->isDestructor()
                )
                && !in_array(
                    $method->getName(),
                    ['__sleep', '__wakeup', '__clone']
                )
            ) {
                $methods[] = $this->_getMethodInfo($method);
            }
        }

        return $methods;
    }

    /**
     * @inheritDoc
     */
    protected function _generateCode()
    {
        $typeName = $this->getSourceClassName();
        $reflection = new \ReflectionClass($typeName);

        if ($reflection->isInterface()) {
            $this->_classGenerator->setImplementedInterfaces([$typeName, '\\' . NoninterceptableInterface::class]);
        } else {
            $this->_classGenerator->setExtendedClass($typeName);
            $this->_classGenerator->setImplementedInterfaces(['\\' . NoninterceptableInterface::class]);
        }
        return parent::_generateCode();
    }

    /**
     * Collect method info
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(\ReflectionMethod $method)
    {
        $parameterNames = [];
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->isVariadic() ? '... $' . $parameter->getName() : '$' . $parameter->getName();
            $parameterNames[] = $name;
            $parameters[] = $this->_getMethodParameterInfo($parameter);
        }

        $returnTypeValue = $this->getReturnTypeValue($method);
        $methodInfo = [
            'name' => $method->getName(),
            'parameters' => $parameters,
            'body' => $this->_getMethodBody(
                $method->getName(),
                $parameterNames,
                $returnTypeValue === 'void'
            ),
            'docblock' => ['shortDescription' => '@inheritDoc'],
            'returntype' => $returnTypeValue,
        ];

        return $methodInfo;
    }

    /**
     * @inheritDoc
     */
    protected function _getDefaultConstructorDefinition()
    {
        return [
            'name' => '__construct',
            'parameters' => [
                ['name' => 'deferred', 'type' => '\\' . DeferredInterface::class],
            ],
            'body' => "\$this->deferred = \$deferred;",
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\\' . DefinitionFactory::class .' $objectManager',
                    ],
                ],
            ]
        ];
    }

    /**
     * Build proxy method body
     *
     * @param string $name
     * @param array $parameters
     * @param bool $withoutReturn
     * @return string
     */
    protected function _getMethodBody(
        $name,
        array $parameters = [],
        bool $withoutReturn = false
    ) {
        if (count($parameters) == 0) {
            $methodCall = sprintf('%s()', $name);
        } else {
            $methodCall = sprintf('%s(%s)', $name, implode(', ', $parameters));
        }

        //Waiting for deferred result and using it's methods.
        return "\$this->wait();\n"
            .($withoutReturn ? '' : 'return ')."\$this->instance->$methodCall;";
    }

    /**
     * @inheritDoc
     */
    protected function _validateData()
    {
        $result = parent::_validateData();
        if ($result) {
            $sourceClassName = $this->getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . '\\ProxyDeferred') {
                $this->_addError(
                    'Invalid ProxyDeferred class name [' . $resultClassName. ']. Use '
                    . $sourceClassName . '\\ProxyDeferred'
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
            $returnTypeValue = ($returnType->allowsNull() ? '?' : '');
            $returnTypeValue .= ($returnType->getName() === 'self')
                ? $this->_getFullyQualifiedClassName($method->getDeclaringClass()->getName())
                : $returnType->getName();
        }

        return $returnTypeValue;
    }
}
