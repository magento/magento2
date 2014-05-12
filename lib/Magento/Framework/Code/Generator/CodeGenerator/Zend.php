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
namespace Magento\Framework\Code\Generator\CodeGenerator;

use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

class Zend extends \Zend\Code\Generator\ClassGenerator implements
    \Magento\Framework\Code\Generator\CodeGenerator\CodeGeneratorInterface
{
    /**
     * Possible doc block options
     *
     * @var array
     */
    protected $_docBlockOptions = array(
        'shortDescription' => 'setShortDescription',
        'longDescription' => 'setLongDescription',
        'tags' => 'setTags'
    );

    /**
     * Possible class property options
     *
     * @var array
     */
    protected $_propertyOptions = array(
        'name' => 'setName',
        'const' => 'setConst',
        'static' => 'setStatic',
        'visibility' => 'setVisibility',
        'defaultValue' => 'setDefaultValue'
    );

    /**
     * Possible class method options
     *
     * @var array
     */
    protected $_methodOptions = array(
        'name' => 'setName',
        'final' => 'setFinal',
        'static' => 'setStatic',
        'abstract' => 'setAbstract',
        'visibility' => 'setVisibility',
        'body' => 'setBody'
    );

    /**
     * Possible method parameter options
     *
     * @var array
     */
    protected $_parameterOptions = array(
        'name' => 'setName',
        'type' => 'setType',
        'defaultValue' => 'setDefaultValue',
        'passedByReference' => 'setPassedByReference'
    );

    /**
     * @param object $object
     * @param array $data
     * @param array $map
     * @return void
     */
    protected function _setDataToObject($object, array $data, array $map)
    {
        foreach ($map as $arrayKey => $setterName) {
            if (isset($data[$arrayKey])) {
                $object->{$setterName}($data[$arrayKey]);
            }
        }
    }

    /**
     * Set class dock block
     *
     * @param array $docBlock
     * @return $this
     */
    public function setClassDocBlock(array $docBlock)
    {
        $docBlockObject = new \Zend\Code\Generator\DocBlockGenerator();
        $this->_setDataToObject($docBlockObject, $docBlock, $this->_docBlockOptions);

        return parent::setDocBlock($docBlockObject);
    }

    /**
     * addMethods()
     *
     * @param array $methods
     * @return $this
     */
    public function addMethods(array $methods)
    {
        foreach ($methods as $methodOptions) {
            $methodObject = new MethodGenerator();
            $this->_setDataToObject($methodObject, $methodOptions, $this->_methodOptions);

            if (isset(
                $methodOptions['parameters']
            ) && is_array(
                $methodOptions['parameters']
            ) && count(
                $methodOptions['parameters']
            ) > 0
            ) {
                $parametersArray = array();
                foreach ($methodOptions['parameters'] as $parameterOptions) {
                    $parameterObject = new \Zend\Code\Generator\ParameterGenerator();
                    $this->_setDataToObject($parameterObject, $parameterOptions, $this->_parameterOptions);
                    $parametersArray[] = $parameterObject;
                }

                $methodObject->setParameters($parametersArray);
            }

            if (isset($methodOptions['docblock']) && is_array($methodOptions['docblock'])) {
                $docBlockObject = new \Zend\Code\Generator\DocBlockGenerator();
                $this->_setDataToObject($docBlockObject, $methodOptions['docblock'], $this->_docBlockOptions);

                $methodObject->setDocBlock($docBlockObject);
            }

            $this->addMethodFromGenerator($methodObject);
        }
        return $this;
    }

    /**
     * Add method from MethodGenerator
     *
     * @param  MethodGenerator $method
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addMethodFromGenerator(MethodGenerator $method)
    {
        if (!is_string($method->getName())) {
            throw new \InvalidArgumentException('addMethodFromGenerator() expects string for name');
        }

        return parent::addMethodFromGenerator($method);
    }

    /**
     * addProperties()
     *
     * @param array $properties
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addProperties(array $properties)
    {
        foreach ($properties as $propertyOptions) {
            $propertyObject = new PropertyGenerator();
            $this->_setDataToObject($propertyObject, $propertyOptions, $this->_propertyOptions);

            if (isset($propertyOptions['docblock'])) {
                $docBlock = $propertyOptions['docblock'];
                if (is_array($docBlock)) {
                    $docBlockObject = new \Zend\Code\Generator\DocBlockGenerator();
                    $this->_setDataToObject($docBlockObject, $docBlock, $this->_docBlockOptions);
                    $propertyObject->setDocBlock($docBlockObject);
                }
            }

            $this->addPropertyFromGenerator($propertyObject);
        }

        return $this;
    }

    /**
     * Add property from PropertyGenerator
     *
     * @param  PropertyGenerator $property
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addPropertyFromGenerator(PropertyGenerator $property)
    {
        if (!is_string($property->getName())) {
            throw new \InvalidArgumentException('addPropertyFromGenerator() expects string for name');
        }

        return parent::addPropertyFromGenerator($property);
    }
}
