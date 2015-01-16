<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Code\Generator;

class Interceptor extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'interceptor';

    /**
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
        return [
            [
                'name' => 'pluginLocator',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'Object Manager instance',
                    'tags' => [[
                        'name' => 'var',
                        'description' => '\Magento\Framework\ObjectManagerInterface',
                    ]],
                ],
            ],
            [
                'name' => 'pluginList',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'List of plugins',
                    'tags' => [[
                        'name' => 'var',
                        'description' => '\Magento\Framework\Interception\PluginListInterface',
                    ]],
                ]
            ],
            [
                'name' => 'chain',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'Invocation chain',
                    'tags' => [[
                        'name' => 'var',
                        'description' => '\Magento\Framework\Interception\ChainInterface',
                    ]],
                ]
            ],
            [
                'name' => 'subjectType',
                'visibility' => 'protected',
                'docblock' => [
                    'shortDescription' => 'Subject type name',
                    'tags' => [['name' => 'var', 'description' => 'string']],
                ]
            ]
        ];
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        $reflectionClass = new \ReflectionClass($this->_getSourceClassName());
        $constructor = $reflectionClass->getConstructor();
        $parameters = [];
        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                $parameters[] = $this->_getMethodParameterInfo($parameter);
            }
        }

        return [
            'name' => '__construct',
            'parameters' => array_merge(
                [
                    ['name' => 'pluginLocator', 'type' => '\Magento\Framework\ObjectManagerInterface'],
                    ['name' => 'pluginList', 'type' => '\Magento\Framework\Interception\PluginListInterface'],
                    ['name' => 'chain', 'type' => '\Magento\Framework\Interception\ChainInterface'],
                ],
                $parameters
            ),
            'body' => "\$this->pluginLocator = \$pluginLocator;\n" .
            "\$this->pluginList = \$pluginList;\n" .
            "\$this->chain = \$chain;\n" .
            "\$this->subjectType = get_parent_class(\$this);\n" .
            (count(
                $parameters
            ) ? "parent::__construct({$this->_getParameterList(
                $parameters
            )});" : '')
        ];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return mixed
     */
    protected function _getClassMethods()
    {
        $methods = [$this->_getDefaultConstructorDefinition()];

        $methods[] = [
            'name' => '___callParent',
            'parameters' => [
                ['name' => 'method', 'type' => 'string'],
                ['name' => 'arguments', 'type' => 'array'],
            ],
            'body' => 'return call_user_func_array(array(\'parent\', $method), $arguments);',
        ];

        $methods[] = [
            'name' => '__sleep',
            'body' => "if (method_exists(get_parent_class(\$this), '__sleep')) {\n" .
            "    return array_diff(parent::__sleep(), array('pluginLocator', 'pluginList', 'chain', 'subjectType'));" .
            "\n} else {\n" .
            "    return array_keys(get_class_vars(get_parent_class(\$this)));\n" .
            "}\n",
        ];

        $methods[] = [
            'name' => '__wakeup',
            'body' => "\$this->pluginLocator = \\Magento\\Framework\\App\\ObjectManager::getInstance();\n" .
            "\$this->pluginList = \$this->pluginLocator->get('Magento\\Framework\\Interception\\PluginListInterface');\n" .
            "\$this->chain = \$this->pluginLocator->get('Magento\\Framework\\Interception\\ChainInterface');\n" .
            "\$this->subjectType = get_parent_class(\$this);\n",
        ];

        $methods[] = [
            'name' => '___callPlugins',
            'visibility' => 'protected',
            'parameters' => [
                ['name' => 'method', 'type' => 'string'],
                ['name' => 'arguments', 'type' => 'array'],
                ['name' => 'pluginInfo', 'type' => 'array'],
            ],
            'body' => "\$capMethod = ucfirst(\$method);\n" .
            "\$result = null;\n" .
            "if (isset(\$pluginInfo[\\Magento\\Framework\\Interception\\DefinitionInterface::LISTENER_BEFORE])) {\n" .
            "    // Call 'before' listeners\n" .
            "    foreach (\$pluginInfo[\\Magento\\Framework\\Interception\\DefinitionInterface::LISTENER_BEFORE] as \$code) {\n" .
            "        \$beforeResult = call_user_func_array(\n" .
            "            array(\$this->pluginList->getPlugin(\$this->subjectType, \$code), 'before'" .
            ". \$capMethod), array_merge(array(\$this), \$arguments)\n" .
            "        );\n" .
            "        if (\$beforeResult) {\n" .
            "            \$arguments = \$beforeResult;\n" .
            "        }\n" .
            "    }\n" .
            "}\n" .
            "if (isset(\$pluginInfo[\\Magento\\Framework\\Interception\\DefinitionInterface::LISTENER_AROUND])) {\n" .
            "    // Call 'around' listener\n" .
            "    \$chain = \$this->chain;\n" .
            "    \$type = \$this->subjectType;\n" .
            "    \$subject = \$this;\n" .
            "    \$code = \$pluginInfo[\\Magento\\Framework\\Interception\\DefinitionInterface::LISTENER_AROUND];\n" .
            "    \$next = function () use (\$chain, \$type, \$method, \$subject, \$code) {\n" .
            "        return \$chain->invokeNext(\$type, \$method, \$subject, func_get_args(), \$code);\n" .
            "    };\n" .
            "    \$result = call_user_func_array(\n" .
            "        array(\$this->pluginList->getPlugin(\$this->subjectType, \$code), 'around' . \$capMethod),\n" .
            "        array_merge(array(\$this, \$next), \$arguments)\n" .
            "    );\n" .
            "} else {\n" .
            "    // Call original method\n" .
            "    \$result = call_user_func_array(array('parent', \$method), \$arguments);\n" .
            "}\n" .
            "if (isset(\$pluginInfo[\\Magento\\Framework\\Interception\\DefinitionInterface::LISTENER_AFTER])) {\n" .
            "    // Call 'after' listeners\n" .
            "    foreach (\$pluginInfo[\\Magento\\Framework\\Interception\\DefinitionInterface::LISTENER_AFTER] as \$code) {\n" .
            "        \$result = \$this->pluginList->getPlugin(\$this->subjectType, \$code)\n" .
            "            ->{'after' . \$capMethod}(\$this, \$result);\n" .
            "    }\n" .
            "}\n" .
            "return \$result;\n",
        ];

        $reflectionClass = new \ReflectionClass($this->_getSourceClassName());
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if ($this->isInterceptedMethod($method)) {
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
        return !($method->isConstructor() ||
            $method->isFinal() ||
            $method->isStatic() ||
            $method->isDestructor()) && !in_array(
                $method->getName(),
                ['__sleep', '__wakeup', '__clone']
            );
    }

    /**
     * Retrieve method info
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(\ReflectionMethod $method)
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $this->_getMethodParameterInfo($parameter);
        }

        $methodInfo = [
            'name' => $method->getName(),
            'parameters' => $parameters,
            'body' => "\$pluginInfo = \$this->pluginList->getNext(\$this->subjectType, '{$method->getName()}');\n" .
            "if (!\$pluginInfo) {\n" .
            "    return parent::{$method->getName()}({$this->_getParameterList(
                $parameters
            )});\n" .
            "} else {\n" .
            "    return \$this->___callPlugins('{$method->getName()}', func_get_args(), \$pluginInfo);\n" .
            "}",
            'docblock' => ['shortDescription' => '{@inheritdoc}'],
        ];

        return $methodInfo;
    }

    /**
     * @param array $parameters
     * @return string
     */
    protected function _getParameterList(array $parameters)
    {
        return implode(
            ', ',
            array_map(
                function ($item) {
                    return "$" . $item['name'];
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
        $typeName = $this->_getFullyQualifiedClassName($this->_getSourceClassName());
        $reflection = new \ReflectionClass($typeName);

        if ($reflection->isInterface()) {
            $this->_classGenerator->setImplementedInterfaces([$typeName]);
        } else {
            $this->_classGenerator->setExtendedClass($typeName);
        }
        return parent::_generateCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function _validateData()
    {
        $result = parent::_validateData();

        if ($result) {
            $sourceClassName = $this->_getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . '\\Interceptor') {
                $this->_addError(
                    'Invalid Interceptor class name [' .
                    $resultClassName .
                    ']. Use ' .
                    $sourceClassName .
                    '\\Interceptor'
                );
                $result = false;
            }
        }
        return $result;
    }
}
