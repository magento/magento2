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
 * @category    Magento
 * @package     Magento_Di
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Di_Generator_Proxy extends Magento_Di_Generator_EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'proxy';

    /**
     * @return array
     */
    protected function _getClassMethods()
    {
        $construct = $this->_getDefaultConstructorDefinition();

        // create proxy methods for all non-static and non-final public methods (excluding constructor)
        $methods         = array($construct);
        $reflectionClass = new ReflectionClass($this->_getSourceClassName());
        $publicMethods   = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() || $method->isFinal() || $method->isStatic())) {
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
        $this->_classGenerator->setExtendedClass($this->_getFullyQualifiedClassName($this->_getSourceClassName()));

        return parent::_generateCode();
    }

    /**
     * Collect method info
     *
     * @param ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(ReflectionMethod $method)
    {
        $parameterNames = array();
        $parameters     = array();
        foreach ($method->getParameters() as $parameter) {
            $parameterNames[] = '$' . $parameter->getName();
            $parameters[]     = $this->_getMethodParameterInfo($parameter);
        }

        $methodInfo = array(
            'name'       => $method->getName(),
            'parameters' => $parameters,
            'body'       => $this->_getMethodBody($method->getName(), $parameterNames),
            'docblock'   => array(
                'shortDescription' => '{@inheritdoc}',
            ),
        );

        return $methodInfo;
    }

    /**
     * Collect method parameter info
     *
     * @param ReflectionParameter $parameter
     * @return array
     */
    protected function _getMethodParameterInfo(ReflectionParameter $parameter)
    {
        $parameterInfo = array(
            'name'              => $parameter->getName(),
            'passedByReference' => $parameter->isPassedByReference()
        );

        if ($parameter->isArray()) {
            $parameterInfo['type'] = 'array';
        } elseif ($parameter->getClass()) {
            $parameterInfo['type'] = $this->_getFullyQualifiedClassName($parameter->getClass()->getName());
        }

        if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
            $defaultValue = $parameter->getDefaultValue();
            if (is_string($defaultValue)) {
                $parameterInfo['defaultValue'] = $this->_escapeDefaultValue($parameter->getDefaultValue());
            } elseif ($defaultValue === null) {
                $parameterInfo['defaultValue'] = $this->_getNullDefaultValue();
            } else {
                $parameterInfo['defaultValue'] = $defaultValue;
            }
        }

        return $parameterInfo;
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

        return 'return $this->_objectManager->get(self::CLASS_NAME)->' . $methodCall . ';';
    }
}
