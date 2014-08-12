<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return array(
            array(
                'name' => 'pluginLocator',
                'visibility' => 'protected',
                'docblock' => array(
                    'shortDescription' => 'Object Manager instance',
                    'tags' => array(array('name' => 'var', 'description' => '\Magento\Framework\ObjectManager'))
                )
            ),
            array(
                'name' => 'pluginList',
                'visibility' => 'protected',
                'docblock' => array(
                    'shortDescription' => 'List of plugins',
                    'tags' => array(array('name' => 'var', 'description' => '\Magento\Framework\Interception\PluginList'))
                )
            ),
            array(
                'name' => 'chain',
                'visibility' => 'protected',
                'docblock' => array(
                    'shortDescription' => 'Invocation chain',
                    'tags' => array(array('name' => 'var', 'description' => '\Magento\Framework\Interception\Chain'))
                )
            ),
            array(
                'name' => 'subjectType',
                'visibility' => 'protected',
                'docblock' => array(
                    'shortDescription' => 'Subject type name',
                    'tags' => array(array('name' => 'var', 'description' => 'string'))
                )
            )
        );
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
        $parameters = array();
        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                $parameters[] = $this->_getMethodParameterInfo($parameter);
            }
        }

        return array(
            'name' => '__construct',
            'parameters' => array_merge(
                array(
                    array('name' => 'pluginLocator', 'type' => '\Magento\Framework\ObjectManager'),
                    array('name' => 'pluginList', 'type' => '\Magento\Framework\Interception\PluginList'),
                    array('name' => 'chain', 'type' => '\Magento\Framework\Interception\Chain')
                ),
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
        );
    }

    /**
     * Returns list of methods for class generator
     *
     * @return mixed
     */
    protected function _getClassMethods()
    {
        $methods = array($this->_getDefaultConstructorDefinition());

        $methods[] = array(
            'name' => '___callParent',
            'parameters' => array(
                array('name' => 'method', 'type' => 'string'),
                array('name' => 'arguments', 'type' => 'array')
            ),
            'body' => 'return call_user_func_array(array(\'parent\', $method), $arguments);'
        );

        $methods[] = array(
            'name' => '__sleep',
            'body' => "if (method_exists(get_parent_class(\$this), '__sleep')) {\n" .
            "    return array_diff(parent::__sleep(), array('pluginLocator', 'pluginList', 'chain', 'subjectType'));" .
            "\n} else {\n" .
            "    return array_keys(get_class_vars(get_parent_class(\$this)));\n" .
            "}\n"
        );

        $methods[] = array(
            'name' => '__wakeup',
            'body' => "\$this->pluginLocator = \\Magento\\Framework\\App\\ObjectManager::getInstance();\n" .
            "\$this->pluginList = \$this->pluginLocator->get('Magento\\Framework\\Interception\\PluginList');\n" .
            "\$this->chain = \$this->pluginLocator->get('Magento\\Framework\\Interception\\Chain');\n" .
            "\$this->subjectType = get_parent_class(\$this);\n"
        );

        $methods[] = array(
            'name' => '___call',
            'visibility' => 'protected',
            'parameters' => array(
                array('name' => 'method', 'type' => 'string'),
                array('name' => 'arguments', 'type' => 'array'),
                array('name' => 'pluginInfo', 'type' => 'array')
            ),
            'body' => "\$capMethod = ucfirst(\$method);\n" .
            "\$result = null;\n" .
            "if (isset(\$pluginInfo[\\Magento\\Framework\\Interception\\Definition::LISTENER_BEFORE])) {\n" .
            "    foreach (\$pluginInfo[\\Magento\\Framework\\Interception\\Definition::LISTENER_BEFORE] as \$code) {\n" .
            "        \$beforeResult = call_user_func_array(\n" .
            "            array(\$this->pluginList->getPlugin(\$this->subjectType, \$code), 'before'" .
            ". \$capMethod), array_merge(array(\$this), \$arguments)\n" .
            "        );\n" .
            "        if (\$beforeResult) {\n" .
            "            \$arguments = \$beforeResult;\n" .
            "        }\n" .
            "    }\n" .
            "}\n" .
            "if (isset(\$pluginInfo[\\Magento\\Framework\\Interception\\Definition::LISTENER_AROUND])) {\n" .
            "    \$chain = \$this->chain;\n" .
            "    \$type = \$this->subjectType;\n" .
            "    \$subject = \$this;\n" .
            "    \$code = \$pluginInfo[\\Magento\\Framework\\Interception\\Definition::LISTENER_AROUND];\n" .
            "    \$next = function () use (\$chain, \$type, \$method, \$subject, \$code) {\n" .
            "        return \$chain->invokeNext(\$type, \$method, \$subject, func_get_args(), \$code);\n" .
            "    };\n" .
            "    \$result = call_user_func_array(\n" .
            "        array(\$this->pluginList->getPlugin(\$this->subjectType, \$code), 'around' . \$capMethod),\n" .
            "        array_merge(array(\$this, \$next), \$arguments)\n" .
            "    );\n" .
            "} else {\n" .
            "    \$result = call_user_func_array(array('parent', \$method), \$arguments);\n" .
            "}\n" .
            "if (isset(\$pluginInfo[\\Magento\\Framework\\Interception\\Definition::LISTENER_AFTER])) {\n" .
            "    foreach (\$pluginInfo[\\Magento\\Framework\\Interception\\Definition::LISTENER_AFTER] as \$code) {\n" .
            "        \$result = \$this->pluginList->getPlugin(\$this->subjectType, \$code)\n" .
            "            ->{'after' . \$capMethod}(\$this, \$result);\n" .
            "    }\n" .
            "}\n" .
            "return \$result;\n"
        );

        $reflectionClass = new \ReflectionClass($this->_getSourceClassName());
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() ||
                $method->isFinal() ||
                $method->isStatic() ||
                $method->isDestructor()) && !in_array(
                    $method->getName(),
                    array('__sleep', '__wakeup', '__clone')
                )
            ) {
                $methods[] = $this->_getMethodInfo($method);
            }
        }

        return $methods;
    }

    /**
     * Retrieve method info
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(\ReflectionMethod $method)
    {
        $parameters = array();
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $this->_getMethodParameterInfo($parameter);
        }

        $methodInfo = array(
            'name' => $method->getName(),
            'parameters' => $parameters,
            'body' => "\$pluginInfo = \$this->pluginList->getNext(\$this->subjectType, '{$method->getName()}');\n" .
            "if (!\$pluginInfo) {\n" .
            "    return parent::{$method->getName()}({$this->_getParameterList(
                $parameters
            )});\n" .
            "} else {\n" .
            "    return \$this->___call('{$method->getName()}', func_get_args(), \$pluginInfo);\n" .
            "}",
            'docblock' => array('shortDescription' => '{@inheritdoc}')
        );

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
            $this->_classGenerator->setImplementedInterfaces(array($typeName));
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
