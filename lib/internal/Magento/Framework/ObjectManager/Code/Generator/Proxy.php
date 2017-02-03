<?php
/**
 * Proxy generator
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

class Proxy extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'proxy';

    /**
     * Marker interface
     */
    const NON_INTERCEPTABLE_INTERFACE = '\Magento\Framework\ObjectManager\NoninterceptableInterface';

    /**
     * @param string $modelClassName
     * @return string
     */
    protected function _getDefaultResultClassName($modelClassName)
    {
        return $modelClassName . '_' . ucfirst(static::ENTITY_TYPE);
    }

    /**
     * Retrieve class properties
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        $properties = parent::_getClassProperties();

        // protected $_instanceName = null;
        $properties[] = [
            'name' => '_instanceName',
            'visibility' => 'protected',
            'docblock' => [
                'shortDescription' => 'Proxied instance name',
                'tags' => [['name' => 'var', 'description' => 'string']],
            ],
        ];

        $properties[] = [
            'name' => '_subject',
            'visibility' => 'protected',
            'docblock' => [
                'shortDescription' => 'Proxied instance',
                'tags' => [['name' => 'var', 'description' => $this->getSourceClassName()]],
            ],
        ];

        // protected $_shared = null;
        $properties[] = [
            'name' => '_isShared',
            'visibility' => 'protected',
            'docblock' => [
                'shortDescription' => 'Instance shareability flag',
                'tags' => [['name' => 'var', 'description' => 'bool']],
            ],
        ];
        return $properties;
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        $construct = $this->_getDefaultConstructorDefinition();

        // create proxy methods for all non-static and non-final public methods (excluding constructor)
        $methods = [$construct];
        $methods[] = [
            'name' => '__sleep',
            'body' => 'return array(\'_subject\', \'_isShared\');',
            'docblock' => ['tags' => [['name' => 'return', 'description' => 'array']]],
        ];
        $methods[] = [
            'name' => '__wakeup',
            'body' => '$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();',
            'docblock' => ['shortDescription' => 'Retrieve ObjectManager from global scope'],
        ];
        $methods[] = [
            'name' => '__clone',
            'body' => "\$this->_subject = clone \$this->_getSubject();",
            'docblock' => ['shortDescription' => 'Clone proxied instance'],
        ];

        $methods[] = [
            'name' => '_getSubject',
            'visibility' => 'protected',
            'body' => "if (!\$this->_subject) {\n" .
            "    \$this->_subject = true === \$this->_isShared\n" .
            "        ? \$this->_objectManager->get(\$this->_instanceName)\n" .
            "        : \$this->_objectManager->create(\$this->_instanceName);\n" .
            "}\n" .
            "return \$this->_subject;",
            'docblock' => [
                'shortDescription' => 'Get proxied instance',
                'tags' => [['name' => 'return', 'description' => $this->getSourceClassName()]],
            ],
        ];
        $reflectionClass = new \ReflectionClass($this->getSourceClassName());
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() ||
                $method->isFinal() ||
                $method->isStatic() ||
                $method->isDestructor()) && !in_array(
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
     * @return string
     */
    protected function _generateCode()
    {
        $typeName = $this->getSourceClassName();
        $reflection = new \ReflectionClass($typeName);

        if ($reflection->isInterface()) {
            $this->_classGenerator->setImplementedInterfaces([$typeName, self::NON_INTERCEPTABLE_INTERFACE]);
        } else {
            $this->_classGenerator->setExtendedClass($typeName);
            $this->_classGenerator->setImplementedInterfaces([self::NON_INTERCEPTABLE_INTERFACE]);
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
            $parameterNames[] = '$' . $parameter->getName();
            $parameters[] = $this->_getMethodParameterInfo($parameter);
        }

        $methodInfo = [
            'name' => $method->getName(),
            'parameters' => $parameters,
            'body' => $this->_getMethodBody($method->getName(), $parameterNames),
            'docblock' => ['shortDescription' => '{@inheritdoc}'],
        ];

        return $methodInfo;
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        /*
         * public function __construct(
         *  \Magento\Framework\ObjectManagerInterface $objectManager,
         *  $instanceName,
         *  $shared = false
         * )
         */
        return [
            'name' => '__construct',
            'parameters' => [
                ['name' => 'objectManager', 'type' => '\Magento\Framework\ObjectManagerInterface'],
                ['name' => 'instanceName', 'defaultValue' => $this->getSourceClassName()],
                ['name' => 'shared', 'defaultValue' => true],
            ],
            'body' => "\$this->_objectManager = \$objectManager;" .
            "\n\$this->_instanceName = \$instanceName;" .
            "\n\$this->_isShared = \$shared;",
            'docblock' => [
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => [
                    [
                        'name' => 'param',
                        'description' => '\Magento\Framework\ObjectManagerInterface $objectManager',
                    ],
                    ['name' => 'param', 'description' => 'string $instanceName'],
                    ['name' => 'param', 'description' => 'bool $shared'],
                ],
            ]
        ];
    }

    /**
     * Build proxy method body
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    protected function _getMethodBody($name, array $parameters = [])
    {
        if (count($parameters) == 0) {
            $methodCall = sprintf('%s()', $name);
        } else {
            $methodCall = sprintf('%s(%s)', $name, implode(', ', $parameters));
        }
        return 'return $this->_getSubject()->' . $methodCall . ';';
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $result = parent::_validateData();
        if ($result) {
            $sourceClassName = $this->getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . '\\Proxy') {
                $this->_addError(
                    'Invalid Proxy class name [' . $resultClassName . ']. Use ' . $sourceClassName . '\\Proxy'
                );
                $result = false;
            }
        }
        return $result;
    }
}
