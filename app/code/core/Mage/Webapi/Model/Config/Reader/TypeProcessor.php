<?php
use Zend\Code\Reflection\ClassReflection;

/**
 * Type processor of config reader properties
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Config_Reader_TypeProcessor
{
    /** @var Mage_Webapi_Helper_Config */
    protected $_helper;

    /**
     * Array of types data.
     *
     * @var array
     */
    protected $_types;

    /**
     * Types class map.
     *
     * @var array
     */
    protected $_typeToClassMap;

    /**
     * Construct type processor.
     *
     * @param Mage_Webapi_Helper_Config $helper
     */
    public function __construct(Mage_Webapi_Helper_Config $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * Retrieve processed types data.
     *
     * @return array
     */
    public function getTypesData()
    {
        return $this->_types;
    }

    /**
     * Retrieve processed types to class map.
     *
     * @return array
     */
    public function getTypeToClassMap()
    {
        return $this->_typeToClassMap;
    }

    /**
     * Process type name.
     * In case parameter type is a complex type (class) - process its properties.
     *
     * @param string $type
     * @return string
     */
    public function process($type)
    {
        $typeName = $this->_helper->normalizeType($type);
        if (!$this->_helper->isTypeSimple($typeName)) {
            $complexTypeName = $this->_helper->translateTypeName($type);
            if (!isset($this->_types[$complexTypeName])) {
                $this->_processComplexType($type);
                if (!$this->_helper->isArrayType($complexTypeName)) {
                    $this->_typeToClassMap[$complexTypeName] = $type;
                }
            }
            $typeName = $complexTypeName;
        }

        return $typeName;
    }

    /**
     * Retrieve complex type information from class public properties.
     *
     * @param string $class
     * @return array
     * @throws InvalidArgumentException
     */
    protected function _processComplexType($class)
    {
        $typeName = $this->_helper->translateTypeName($class);
        $this->_types[$typeName] = array();
        if ($this->_helper->isArrayType($class)) {
            $this->process($this->_helper->getArrayItemType($class));
        } else {
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('Could not load the "%s" class as parameter type.', $class));
            }
            $reflection = new ClassReflection($class);
            $docBlock = $reflection->getDocBlock();
            $this->_types[$typeName]['documentation'] = $docBlock ? $this->_getDescription($docBlock) : '';
            $defaultProperties = $reflection->getDefaultProperties();
            /** @var \Zend\Code\Reflection\PropertyReflection $property */
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                $this->_processProperty($property, $defaultProperties, $typeName);
            }
        }

        return $this->_types[$typeName];
    }

    /**
     * Process class property.
     *
     * @param Zend\Code\Reflection\PropertyReflection $property
     * @param $defaultProperties
     * @param $typeName
     * @throws InvalidArgumentException
     */
    protected function _processProperty(
        \Zend\Code\Reflection\PropertyReflection $property,
        $defaultProperties,
        $typeName
    ) {
        $propertyName = $property->getName();
        $propertyDocBlock = $property->getDocBlock();
        if (!$propertyDocBlock) {
            throw new InvalidArgumentException('Each property must have description with @var annotation.');
        }
        $varTags = $propertyDocBlock->getTags('var');
        if (empty($varTags)) {
            throw new InvalidArgumentException('Property type must be defined with @var tag.');
        }
        /** @var \Zend\Code\Reflection\DocBlock\Tag\GenericTag $varTag */
        $varTag = current($varTags);
        $varContentParts = explode(' ', $varTag->getContent(), 2);
        $varType = current($varContentParts);
        $varInlineDoc = (count($varContentParts) > 1) ? end($varContentParts) : '';
        $optionalTags = $propertyDocBlock->getTags('optional');
        if (!empty($optionalTags)) {
            /** @var \Zend\Code\Reflection\DocBlock\Tag\GenericTag $isOptionalTag */
            $isOptionalTag = current($optionalTags);
            $isOptional = $isOptionalTag->getName() == 'optional';
        } else {
            $isOptional = false;
        }

        $this->_types[$typeName]['parameters'][$propertyName] = array(
            'type' => $this->process($varType),
            'required' => !$isOptional && is_null($defaultProperties[$propertyName]),
            'default' => $defaultProperties[$propertyName],
            'documentation' => $varInlineDoc . $this->_getDescription($propertyDocBlock)
        );
    }

    /**
     * Get short and long description from docblock and concatenate.
     *
     * @param Zend\Code\Reflection\DocBlockReflection $doc
     * @return string
     */
    protected function _getDescription(\Zend\Code\Reflection\DocBlockReflection $doc)
    {
        $shortDescription = $doc->getShortDescription();
        $longDescription = $doc->getLongDescription();

        $description = $shortDescription;
        if ($longDescription && !empty($description)) {
            $description .= "\r\n";
        }
        $description .= $longDescription;

        return $description;
    }
}
