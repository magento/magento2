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
 * @package     Magento_Code
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Service\Code\Generator;

use Magento\Framework\Code\Generator\EntityAbstract;

/**
 * Class Builder
 */
class Builder extends EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'builder';

    /**
     * Retrieve class properties
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
        return [];
    }

    /**
     * Returns list of methods for class generator
     *
     * @return array
     */
    protected function _getClassMethods()
    {
        $methods = [];
        $reflectionClass = new \ReflectionClass($this->_getSourceClassName());
        $publicMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            if (!($method->isConstructor() ||
                    $method->isFinal() ||
                    $method->isStatic() ||
                    $method->isDestructor()) &&
                !in_array(
                    $method->getName(),
                    array('__sleep', '__wakeup', '__clone')
                ) &&
                $method->class !== 'Magento\Framework\Service\Data\AbstractExtensibleObject'
            ) {
                if (substr($method->getName(), 0, 3) == 'get') {
                    $methods[] = $this->_getMethodInfo($reflectionClass, $method);
                }

            }
        }
        return $methods;
    }

    /**
     * Retrieve method info
     *
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     * @return array
     */
    protected function _getMethodInfo(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $methodInfo = [
            'name' => 'set' . substr($method->getName(), 3),
            'parameters' => [
                ['name' => lcfirst(substr($method->getName(), 3))]
            ],
            'body' => "\$this->_set("
                . '\\' . $class->getName() . "::"
                . strtoupper(preg_replace('/(.)([A-Z])/', "$1_$2", substr($method->getName(), 3)))
                . ", \$" . lcfirst(substr($method->getName(), 3)) . ");",
            'docblock' => array('shortDescription' => '{@inheritdoc}')
        ];

        return $methodInfo;
    }

    /**
     * Validate data
     *
     * @return bool
     */
    protected function _validateData()
    {
        $result = parent::_validateData();

        if ($result) {
            $sourceClassName = $this->_getSourceClassName();
            $resultClassName = $this->_getResultClassName();

            if ($resultClassName !== $sourceClassName . 'Builder') {
                $this->_addError(
                    'Invalid Builder class name [' . $resultClassName . ']. Use ' . $sourceClassName . 'Builder'
                );
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Generate code
     *
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator->setName(
            $this->_getResultClassName()
        )->addProperties(
            $this->_getClassProperties()
        )->addMethods(
            $this->_getClassMethods()
        )->setClassDocBlock(
            $this->_getClassDocBlock()
        )->setExtendedClass('\\Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder');

        return $this->_getGeneratedCode();
    }
}
