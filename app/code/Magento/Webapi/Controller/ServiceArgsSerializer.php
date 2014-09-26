<?php
/**
 * Service Args Serializer
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
namespace Magento\Webapi\Controller;

use Magento\Framework\ObjectManager;
use Magento\Framework\Service\Config\Reader as ServiceConfigReader;
use Magento\Framework\Service\Data\AttributeValue;
use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Webapi\Model\Config\ClassReflector\TypeProcessor;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Reflection\ParameterReflection;
use Magento\Framework\Service\SimpleDataObjectConverter;

class ServiceArgsSerializer
{
    /** @var \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor */
    protected $_typeProcessor;

    /** @var ObjectManager */
    protected $_objectManager;

    /** @var ServiceConfigReader */
    protected $serviceConfigReader;

    /** @var AttributeValueBuilder */
    protected $attributeValueBuilder;

    /**
     * Initialize dependencies.
     *
     * @param TypeProcessor $typeProcessor
     * @param ObjectManager $objectManager
     * @param ServiceConfigReader $serviceConfigReader
     * @param AttributeValueBuilder $attributeValueBuilder
     */
    public function __construct(
        TypeProcessor $typeProcessor,
        ObjectManager $objectManager,
        ServiceConfigReader $serviceConfigReader,
        AttributeValueBuilder $attributeValueBuilder
    ) {
        $this->_typeProcessor = $typeProcessor;
        $this->_objectManager = $objectManager;
        $this->serviceConfigReader = $serviceConfigReader;
        $this->attributeValueBuilder = $attributeValueBuilder;
    }

    /**
     * Converts the provided input array from key-value format to a list of parameters suitable for the specified
     * class / method.
     *
     * The input array should have the field name as the key, and the value will either be a primitive or another
     * key-value array.  The top level of this array needs keys that match the names of the parameters on the
     * service method.
     *
     * Mismatched types are caught by the PHP runtime, not explicitly checked for by this code.
     *
     * @param string $serviceClassName name of the service class that we are trying to call
     * @param string $serviceMethodName name of the method that we are trying to call
     * @param array $inputArray data to send to method in key-value format
     * @return array list of parameters that can be used to call the service method
     */
    public function getInputData($serviceClassName, $serviceMethodName, array $inputArray)
    {
        /** TODO: Reflection causes performance degradation when used in runtime. Should be optimized via caching */
        $serviceClass = new ClassReflection($serviceClassName);
        /** @var MethodReflection $serviceMethod */
        $serviceMethod = $serviceClass->getMethod($serviceMethodName);
        /** @var ParameterReflection[] $params */
        $params = $serviceMethod->getParameters();

        $inputData = [];
        foreach ($params as $param) {
            $paramName = $param->getName();
            $snakeCaseParamName = strtolower(preg_replace("/(?<=\\w)(?=[A-Z])/", "_$1", $paramName));
            if (isset($inputArray[$paramName]) || isset($inputArray[$snakeCaseParamName])) {
                $paramValue = isset($inputArray[$paramName])
                    ? $inputArray[$paramName]
                    : $inputArray[$snakeCaseParamName];

                if ($this->_isArrayParam($param)) {
                    $paramType = "{$param->getType()}[]";
                } else {
                    $paramType = $param->getType();
                }
                $inputData[] = $this->_convertValue($paramValue, $paramType);
            } else {
                $inputData[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
            }
        }

        return $inputData;
    }

    /**
     * Check if parameter is an array.
     *
     * @param ParameterReflection $param
     * @return bool
     */
    protected function _isArrayParam($param)
    {
        $isArray = $param->isArray();
        $docBlock = $param->getDeclaringFunction()->getDocBlock();
        /** If array type is not set explicitly in the method interface, examine annotations */
        if (!$isArray && $docBlock) {
            /** This pattern will help to skip parameters declarations which precede to the current one */
            $precedingParamsPattern = str_repeat('.*\@param.*', $param->getPosition());
            $paramType = str_replace('\\', '\\\\', $param->getType());
            if (preg_match("/.*{$precedingParamsPattern}\@param\s+({$paramType}\[\]).*/is", $docBlock->getContents())) {
                $isArray = true;
            }
        }
        return $isArray;
    }

    /**
     * Creates a new instance of the given class and populates it with the array of data. The data can
     * be in different forms depending on the adapter being used, REST vs. SOAP. For REST, the data is
     * in snake_case (e.g. tax_class_id) while for SOAP the data is in camelCase (e.g. taxClassId).
     *
     * @param string|\ReflectionClass $class
     * @param array $data
     * @return object the newly created and populated object
     */
    protected function _createFromArray($class, $data)
    {
        $className = is_string($class) ? $class : $class->getName();
        $data = is_array($data) ? $data : [];
        $builder = $this->_objectManager->create($className . "Builder");
        $class = new ClassReflection($className);
        foreach ($data as $propertyName => $value) {
            // Converts snake_case to uppercase CamelCase to help form getter/setter method names
            // This use case is for REST only. SOAP request data is already camel cased
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            $methodName = $this->_processGetterMethod($class, $camelCaseProperty);
            $methodReflection = $class->getMethod($methodName);
            if ($methodReflection->isPublic()) {
                $returnType = $this->_typeProcessor->getGetterReturnType($methodReflection)['type'];
                $setterName = 'set' . $camelCaseProperty;
                if ($camelCaseProperty === 'CustomAttributes') {
                    $setterValue = $this->convertCustomAttributeValue($value, $returnType, $className);
                } else {
                    $setterValue = $this->_convertValue($value, $returnType);
                }
                $builder->{$setterName}($setterValue);
            }
        }
        return $builder->create();
    }

    /**
     * Convert custom attribute data array to array of AttributeValue Data Object
     *
     * @param array $customAttributesValueArray
     * @param string $returnType
     * @param string $dataObjectClassName
     * @return AttributeValue[]
     */
    protected function convertCustomAttributeValue($customAttributesValueArray, $returnType, $dataObjectClassName)
    {
        $result = [];
        $allAttributes = $this->serviceConfigReader->read();
        $dataObjectClassName = ltrim($dataObjectClassName, '\\');
        if (!isset($allAttributes[$dataObjectClassName])) {
            return $this->_convertValue($customAttributesValueArray, $returnType);
        }
        $dataObjectAttributes = $allAttributes[$dataObjectClassName];
        $camelCaseAttributeCodeKey = lcfirst(
            SimpleDataObjectConverter::snakeCaseToUpperCamelCase(AttributeValue::ATTRIBUTE_CODE)
        );
        foreach ($customAttributesValueArray as $customAttribute) {
            if (isset($customAttribute[AttributeValue::ATTRIBUTE_CODE])) {
                $customAttributeCode = $customAttribute[AttributeValue::ATTRIBUTE_CODE];
            } else if (isset($customAttribute[$camelCaseAttributeCodeKey])) {
                $customAttributeCode = $customAttribute[$camelCaseAttributeCodeKey];
            } else {
                $customAttributeCode = null;
            }

            //Check if type is defined, else default to mixed
            $type = isset($dataObjectAttributes[$customAttributeCode])
                ? $dataObjectAttributes[$customAttributeCode]
                : TypeProcessor::ANY_TYPE;

            $customAttributeValue = $customAttribute[AttributeValue::VALUE];
            if (is_array($customAttributeValue)) {
                //If type for AttributeValue's value as array is mixed, further processing is not possible
                if ($type === TypeProcessor::ANY_TYPE) {
                    continue;
                }
                //If custom attribute value is an array then its a data object type
                $attributeValue = $this->_createFromArray($type, $customAttributeValue);
            } else {
                $attributeValue = $this->_convertValue($customAttributeValue, $type);
            }
            //Populate the attribute value data object once the value for custom attribute is derived based on type
            $result[] = $this->attributeValueBuilder
                ->setAttributeCode($customAttributeCode)
                ->setValue($attributeValue)
                ->create();
        }

        return $result;
    }

    /**
     * Convert data from array to Data Object representation if type is Data Object or array of Data Objects.
     *
     * @param mixed $value
     * @param string $type Convert given value to the this type
     * @return mixed
     */
    protected function _convertValue($value, $type)
    {
        $isArrayType = $this->_typeProcessor->isArrayType($type);
        if ($isArrayType && isset($value['item'])) {
            $value = $this->_removeSoapItemNode($value);
        }
        if ($this->_typeProcessor->isTypeSimple($type) || $this->_typeProcessor->isTypeAny($type)) {
            $result = $this->_typeProcessor->processSimpleAndAnyType($value, $type);
        } else {
            /** Complex type or array of complex types */
            if ($isArrayType) {
                // Initializing the result for array type else it will return null for empty array
                $result = is_array($value) ? [] : null;
                $itemType = $this->_typeProcessor->getArrayItemType($type);
                if (is_array($value)) {
                    foreach ($value as $key => $item) {
                        $result[$key] = $this->_createFromArray($itemType, $item);
                    }
                }
            } else {
                $result = $this->_createFromArray($type, $value);
            }
        }
        return $result;
    }

    /**
     * Find the getter method for a given property in the Data Object class
     *
     * @param ClassReflection $class
     * @param string $camelCaseProperty
     * @return string processed method name
     * @throws \Exception If $camelCaseProperty has no corresponding getter method
     */
    protected function _processGetterMethod(ClassReflection $class, $camelCaseProperty)
    {
        $getterName = 'get' . $camelCaseProperty;
        $boolGetterName = 'is' . $camelCaseProperty;
        if ($class->hasMethod($getterName)) {
            $methodName = $getterName;
        } elseif ($class->hasMethod($boolGetterName)) {
            $methodName = $boolGetterName;
        } else {
            throw new \Exception(
                sprintf(
                    'Property :"%s" does not exist in the Data Object class: "%s".',
                    $camelCaseProperty,
                    $class->getName()
                )
            );
        }
        return $methodName;
    }

    /**
     * Remove item node added by the SOAP server for array types
     *
     * @param array|mixed $value
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _removeSoapItemNode($value)
    {
        if (isset($value['item'])) {
            if (is_array($value['item'])) {
                $value = $value['item'];
            } else {
                return [$value['item']];
            }
        } else {
            throw new \InvalidArgumentException('Value must be an array and must contain "item" field.');
        }
        /**
         * In case when only one Data object value is passed, it will not be wrapped into a subarray
         * within item node. If several Data object values are passed, they will be wrapped into
         * an indexed array within item node.
         */
        $isAssociative = array_keys($value) !== range(0, count($value) - 1);
        return $isAssociative ? [$value] : $value;
    }
}
