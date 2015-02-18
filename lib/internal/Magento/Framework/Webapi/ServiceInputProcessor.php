<?php
/**
 * Service Input Processor
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\Config\Reader as ServiceConfigReader;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Cache\Frontend\Decorator\TagScope as WebapiCache;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialization\DataBuilderFactory;
use Magento\Framework\Webapi\Exception as WebapiException;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;
use Zend\Code\Reflection\ParameterReflection;

/**
 * Deserialize arguments from API requests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceInputProcessor
{
    const CACHE_ID_PREFIX = 'service_method_params_';

    /** @var \Magento\Framework\Reflection\TypeProcessor */
    protected $typeProcessor;

    /** @var DataBuilderFactory */
    protected $builderFactory;

    /** @var ServiceConfigReader */
    protected $serviceConfigReader;

    /** @var AttributeDataBuilder */
    protected $attributeValueBuilder;

    /** @var WebapiCache */
    protected $cache;

    /**
     * Initialize dependencies.
     *
     * @param TypeProcessor $typeProcessor
     * @param DataBuilderFactory $builderFactory
     * @param ServiceConfigReader $serviceConfigReader
     * @param AttributeDataBuilder $attributeValueBuilder
     * @param WebapiCache $cache
     */
    public function __construct(
        TypeProcessor $typeProcessor,
        DataBuilderFactory $builderFactory,
        ServiceConfigReader $serviceConfigReader,
        AttributeDataBuilder $attributeValueBuilder,
        WebapiCache $cache
    ) {
        $this->typeProcessor = $typeProcessor;
        $this->builderFactory = $builderFactory;
        $this->serviceConfigReader = $serviceConfigReader;
        $this->attributeValueBuilder = $attributeValueBuilder;
        $this->cache = $cache;
    }

    /**
     * Convert the input array from key-value format to a list of parameters suitable for the specified class / method.
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
     * @throws InputException if no value is provided for required parameters
     */
    public function process($serviceClassName, $serviceMethodName, array $inputArray)
    {
        $inputData = [];
        $inputError = [];
        foreach ($this->getMethodParams($serviceClassName, $serviceMethodName) as $param) {
            $paramName = $param['name'];
            $snakeCaseParamName = strtolower(preg_replace("/(?<=\\w)(?=[A-Z])/", "_$1", $paramName));
            if (isset($inputArray[$paramName]) || isset($inputArray[$snakeCaseParamName])) {
                $paramValue = isset($inputArray[$paramName])
                    ? $inputArray[$paramName]
                    : $inputArray[$snakeCaseParamName];

                $inputData[] = $this->_convertValue($paramValue, $param['type']);
            } else {
                if ($param['isDefaultValueAvailable']) {
                    $inputData[] = $param['defaultValue'];
                } else {
                    $inputError[] = $paramName;
                }
            }
        }

        if (!empty($inputError)) {
            $exception = new InputException();
            foreach ($inputError as $errorParamField) {
                $exception->addError(InputException::REQUIRED_FIELD, ['fieldName' => $errorParamField]);
            }
            if ($exception->wasErrorAdded()) {
                throw $exception;
            }
        }

        return $inputData;
    }

    /**
     * Creates a new instance of the given class and populates it with the array of data. The data can
     * be in different forms depending on the adapter being used, REST vs. SOAP. For REST, the data is
     * in snake_case (e.g. tax_class_id) while for SOAP the data is in camelCase (e.g. taxClassId).
     *
     * @param string $className
     * @param array $data
     * @return object the newly created and populated object
     */
    protected function _createFromArray($className, $data)
    {
        $data = is_array($data) ? $data : [];
        $class = new ClassReflection($className);

        $builder = $this->builderFactory->getDataBuilder($className);

        foreach ($data as $propertyName => $value) {
            // Converts snake_case to uppercase CamelCase to help form getter/setter method names
            // This use case is for REST only. SOAP request data is already camel cased
            $camelCaseProperty = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($propertyName);
            $methodName = $this->typeProcessor->findGetterMethodName($class, $camelCaseProperty);
            $methodReflection = $class->getMethod($methodName);
            if ($methodReflection->isPublic()) {
                $returnType = $this->typeProcessor->getGetterReturnType($methodReflection)['type'];
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
            } elseif (isset($customAttribute[$camelCaseAttributeCodeKey])) {
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

                $attributeValue = $this->_createDataObjectForTypeAndArrayValue($type, $customAttributeValue);
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
     * Creates a data object type from a given type name and a PHP array.
     *
     * @param string $type The type of data object to create
     * @param array $customAttributeValue The data object values
     * @return mixed
     */
    protected function _createDataObjectForTypeAndArrayValue($type, $customAttributeValue)
    {
        if (substr($type, -2) === "[]") {
            $type = substr($type, 0, -2);
            $attributeValue = [];
            foreach ($customAttributeValue as $value) {
                $attributeValue[] = $this->_createFromArray($type, $value);
            }
        } else {
            $attributeValue = $this->_createFromArray($type, $customAttributeValue);
        }

        return $attributeValue;
    }

    /**
     * Convert data from array to Data Object representation if type is Data Object or array of Data Objects.
     *
     * @param mixed $value
     * @param string $type Convert given value to the this type
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertValue($value, $type)
    {
        $isArrayType = $this->typeProcessor->isArrayType($type);
        if ($isArrayType && isset($value['item'])) {
            $value = $this->_removeSoapItemNode($value);
        }
        if ($this->typeProcessor->isTypeSimple($type) || $this->typeProcessor->isTypeAny($type)) {
            try {
                $result = $this->typeProcessor->processSimpleAndAnyType($value, $type);
            } catch (SerializationException $e) {
                throw new WebapiException($e->getMessage());
            }
        } else {
            /** Complex type or array of complex types */
            if ($isArrayType) {
                // Initializing the result for array type else it will return null for empty array
                $result = is_array($value) ? [] : null;
                $itemType = $this->typeProcessor->getArrayItemType($type);
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

    /**
     * Retrieve requested service method params metadata.
     *
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @return array
     */
    protected function getMethodParams($serviceClassName, $serviceMethodName)
    {
        $cacheId = self::CACHE_ID_PREFIX . hash('md5', $serviceClassName . $serviceMethodName);
        $params = $this->cache->load($cacheId);
        if ($params !== false) {
            return unserialize($params);
        }
        $serviceClass = new ClassReflection($serviceClassName);
        /** @var MethodReflection $serviceMethod */
        $serviceMethod = $serviceClass->getMethod($serviceMethodName);
        $params = [];
        /** @var ParameterReflection $paramReflection */
        foreach ($serviceMethod->getParameters() as $paramReflection) {
            $isDefaultValueAvailable = $paramReflection->isDefaultValueAvailable();
            $params[] = [
                'name' => $paramReflection->getName(),
                'type' => $this->typeProcessor->getParamType($paramReflection),
                'isDefaultValueAvailable' => $isDefaultValueAvailable,
                'defaultValue' => $isDefaultValueAvailable ? $paramReflection->getDefaultValue() : null
            ];
        }
        $this->cache->save(serialize($params), $cacheId, [\Magento\Webapi\Model\Cache\Type::CACHE_TAG]);
        return $params;
    }
}
