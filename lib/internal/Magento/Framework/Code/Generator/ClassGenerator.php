<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

class ClassGenerator extends \Zend\Code\Generator\ClassGenerator implements
    \Magento\Framework\Code\Generator\CodeGeneratorInterface
{
    /**
     * Possible doc block options
     *
     * @var array
     */
    protected $_docBlockOptions = [
        'shortDescription' => 'setShortDescription',
        'longDescription' => 'setLongDescription',
        'tags' => 'setTags',
    ];

    /**
     * Possible class property options
     *
     * @var array
     */
    protected $_propertyOptions = [
        'name' => 'setName',
        'const' => 'setConst',
        'static' => 'setStatic',
        'visibility' => 'setVisibility',
        'defaultValue' => 'setDefaultValue',
    ];

    /**
     * Possible class method options
     *
     * @var array
     */
    protected $_methodOptions = [
        'name' => 'setName',
        'final' => 'setFinal',
        'static' => 'setStatic',
        'abstract' => 'setAbstract',
        'visibility' => 'setVisibility',
        'body' => 'setBody',
    ];

    /**
     * Possible method parameter options
     *
     * @var array
     */
    protected $_parameterOptions = [
        'name' => 'setName',
        'type' => 'setType',
        'defaultValue' => 'setDefaultValue',
        'passedByReference' => 'setPassedByReference',
    ];

    /**
     * @var array Array of string names
     */
    protected $traits = array();

    public function setTraits(array $traits)
    {
        $this->traits = $traits;
        return $this;
    }

    /**
     * Returns the "traits" classes
     *
     * @return array
     */
    public function getTraits()
    {
        return $this->traits;
    }

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
            $methodObject = $this->createMethodGenerator();
            $this->_setDataToObject($methodObject, $methodOptions, $this->_methodOptions);

            if (isset(
                $methodOptions['parameters']
            ) && is_array(
                $methodOptions['parameters']
            ) && count(
                $methodOptions['parameters']
            ) > 0
            ) {
                $parametersArray = [];
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

    /**
     * Instantiate method generator object.
     *
     * @return MethodGenerator
     */
    protected function createMethodGenerator()
    {
        return new MethodGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaceName()
    {
        return ltrim(parent::getNamespaceName(), '\\');
    }

    /**
     * @return string
     */
    public function generate()
    {
        if (!$this->isSourceDirty()) {
            $output = $this->getSourceContent();
            if (!empty($output)) {
                return $output;
            }
        }

        $output = '';

        if (null !== ($namespace = $this->getNamespaceName())) {
            $output .= 'namespace ' . $namespace . ';' . self::LINE_FEED . self::LINE_FEED;
        }

        $uses = $this->getUses();
        if (!empty($uses)) {
            foreach ($uses as $use) {
                $output .= 'use ' . $use . ';' . self::LINE_FEED;
            }
            $output .= self::LINE_FEED;
        }

        if (null !== ($docBlock = $this->getDocBlock())) {
            $docBlock->setIndentation('');
            $output .= $docBlock->generate();
        }

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        }

        $output .= 'class ' . $this->getName();

        if (!empty($this->extendedClass)) {
            $output .= ' extends ' . $this->extendedClass;
        }

        $implemented = $this->getImplementedInterfaces();
        if (!empty($implemented)) {
            $output .= ' implements ' . implode(', ', $implemented);
        }

        $output .= self::LINE_FEED . '{' . self::LINE_FEED . self::LINE_FEED;

        $traits = $this->getTraits();
        if (!empty($traits)) {
            $output .= self::LINE_FEED . $this->indentation
                . 'use ' . (implode(', ', $traits)) . ';' . self::LINE_FEED . self::LINE_FEED;
        }

        $properties = $this->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $property) {
                $output .= $property->generate() . self::LINE_FEED . self::LINE_FEED;
            }
        }

        $methods = $this->getMethods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                $output .= $method->generate() . self::LINE_FEED;
            }
        }

        $output .= self::LINE_FEED . '}' . self::LINE_FEED;

        return $output;
    }
}
