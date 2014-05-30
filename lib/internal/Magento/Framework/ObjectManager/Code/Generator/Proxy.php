<?php
/**
 * Proxy generator
 *
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
namespace Magento\Framework\ObjectManager\Code\Generator;

class Proxy extends \Magento\Framework\Code\Generator\EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'proxy';

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
        $properties[] = array(
            'name' => '_instanceName',
            'visibility' => 'protected',
            'docblock' => array(
                'shortDescription' => 'Proxied instance name',
                'tags' => array(array('name' => 'var', 'description' => 'string'))
            )
        );

        $properties[] = array(
            'name' => '_subject',
            'visibility' => 'protected',
            'docblock' => array(
                'shortDescription' => 'Proxied instance',
                'tags' => array(array('name' => 'var', 'description' => '\\' . $this->_getSourceClassName()))
            )
        );

        // protected $_shared = null;
        $properties[] = array(
            'name' => '_isShared',
            'visibility' => 'protected',
            'docblock' => array(
                'shortDescription' => 'Instance shareability flag',
                'tags' => array(array('name' => 'var', 'description' => 'bool'))
            )
        );
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
        $methods = array($construct);
        $methods[] = array(
            'name' => '__sleep',
            'body' => 'return array(\'_subject\', \'_isShared\');',
            'docblock' => array('tags' => array(array('name' => 'return', 'description' => 'array')))
        );
        $methods[] = array(
            'name' => '__wakeup',
            'body' => '$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();',
            'docblock' => array('shortDescription' => 'Retrieve ObjectManager from global scope')
        );
        $methods[] = array(
            'name' => '__clone',
            'body' => "\$this->_subject = clone \$this->_getSubject();",
            'docblock' => array('shortDescription' => 'Clone proxied instance')
        );

        $methods[] = array(
            'name' => '_getSubject',
            'visibility' => 'protected',
            'body' => "if (!\$this->_subject) {\n" .
            "    \$this->_subject = true === \$this->_isShared\n" .
            "        ? \$this->_objectManager->get(\$this->_instanceName)\n" .
            "        : \$this->_objectManager->create(\$this->_instanceName);\n" .
            "}\n" .
            "return \$this->_subject;",
            'docblock' => array(
                'shortDescription' => 'Get proxied instance',
                'tags' => array(array('name' => 'return', 'description' => '\\' . $this->_getSourceClassName()))
            )
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
     * Collect method info
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(\ReflectionMethod $method)
    {
        $parameterNames = array();
        $parameters = array();
        foreach ($method->getParameters() as $parameter) {
            $parameterNames[] = '$' . $parameter->getName();
            $parameters[] = $this->_getMethodParameterInfo($parameter);
        }

        $methodInfo = array(
            'name' => $method->getName(),
            'parameters' => $parameters,
            'body' => $this->_getMethodBody($method->getName(), $parameterNames),
            'docblock' => array('shortDescription' => '{@inheritdoc}')
        );

        return $methodInfo;
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        // public function __construct(\Magento\Framework\ObjectManager $objectManager, $instanceName, $shared = false)
        return array(
            'name' => '__construct',
            'parameters' => array(
                array('name' => 'objectManager', 'type' => '\Magento\Framework\ObjectManager'),
                array('name' => 'instanceName', 'defaultValue' => $this->_getSourceClassName()),
                array('name' => 'shared', 'defaultValue' => true)
            ),
            'body' => "\$this->_objectManager = \$objectManager;" .
            "\n\$this->_instanceName = \$instanceName;" .
            "\n\$this->_isShared = \$shared;",
            'docblock' => array(
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags' => array(
                    array('name' => 'param', 'description' => '\Magento\Framework\ObjectManager $objectManager'),
                    array('name' => 'param', 'description' => 'string $instanceName'),
                    array('name' => 'param', 'description' => 'bool $shared')
                )
            )
        );
    }

    /**
     * Build proxy method body
     *
     * @param string $name
     * @param array $parameters
     * @return string
     */
    protected function _getMethodBody($name, array $parameters = array())
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
            $sourceClassName = $this->_getSourceClassName();
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
