<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\MethodReflection;

/**
 * Data object processor for de-serialization using class reflection
 */
class DataObjectProcessor
{
    const IS_METHOD_PREFIX = 'is';
    const HAS_METHOD_PREFIX = 'has';
    const GETTER_PREFIX = 'get';
    const SERVICE_INTERFACE_METHODS_CACHE_PREFIX = 'serviceInterfaceMethodsMap';
    const BASE_MODEL_CLASS = 'Magento\Framework\Model\AbstractExtensibleModel';

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $cache;

    /**
     * @var TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var array
     */
    protected $dataInterfaceMethodsMap = [];

    /**
     * @var array
     */
    protected $serviceInterfaceMethodsMap = [];

    /**
     * @var \Magento\Framework\Api\AttributeTypeResolverInterface
     */
    protected $attributeTypeResolver;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param TypeProcessor $typeProcessor
     * @param \Magento\Framework\Api\AttributeTypeResolverInterface $typeResolver
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        TypeProcessor $typeProcessor,
        \Magento\Framework\Api\AttributeTypeResolverInterface $typeResolver
    ) {
        $this->cache = $cache;
        $this->typeProcessor = $typeProcessor;
        $this->attributeTypeResolver = $typeResolver;
    }

    /**
     * Use class reflection on given data interface to build output data array
     *
     * @param mixed $dataObject
     * @param string $dataObjectType
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function buildOutputDataArray($dataObject, $dataObjectType)
    {
        $methods = $this->getMethodsMap($dataObjectType);
        $outputData = [];

        /** @var MethodReflection $method */
        foreach ($methods as $methodName => $methodReflectionData) {
            // any method with parameter(s) gets ignored because we do not know the type and value of
            // the parameter(s), so we are not able to process
            if ($methodReflectionData['parameterCount'] > 0) {
                continue;
            }
            $returnType = $methodReflectionData['type'];
            if (substr($methodName, 0, 2) === self::IS_METHOD_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 2));
                $outputData[$key] = $this->castValueToType($value, $returnType);
            } elseif (substr($methodName, 0, 3) === self::HAS_METHOD_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                $outputData[$key] = $this->castValueToType($value, $returnType);
            } elseif (substr($methodName, 0, 3) === self::GETTER_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($methodName === 'getCustomAttributes' && $value === []) {
                    continue;
                }
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                if ($key === ExtensibleDataInterface::CUSTOM_ATTRIBUTES) {
                    $value = $this->convertCustomAttributes($value, $dataObjectType);
                } elseif (is_object($value)) {
                    $value = $this->buildOutputDataArray($value, $returnType);
                } elseif (is_array($value)) {
                    $valueResult = [];
                    $arrayElementType = substr($returnType, 0, -2);
                    foreach ($value as $singleValue) {
                        if (is_object($singleValue)) {
                            $singleValue = $this->buildOutputDataArray($singleValue, $arrayElementType);
                        }
                        $valueResult[] = $this->castValueToType($singleValue, $arrayElementType);
                    }
                    $value = $valueResult;
                }
                $outputData[$key] = $this->castValueToType($value, $returnType);
            }
        }
        return $outputData;
    }

    /**
     * Cast the output type to the documented type. This helps for output purposes.
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function castValueToType($value, $type)
    {
        if ($value === null) {
            return null;
        }

        if ($type === "int" || $type === "integer") {
            return (int)$value;
        }

        if ($type === "string") {
            return (string)$value;
        }

        if ($type === "bool" || $type === "boolean" || $type === "true" || $type == "false") {
            return (bool)$value;
        }

        if ($type === "float") {
            return (float)$value;
        }

        if ($type === "double") {
            return (double)$value;
        }

        return $value;
    }

    /**
     * Get return type by interface name and method
     *
     * @param string $interfaceName
     * @param string $methodName
     * @return string
     */
    public function getMethodReturnType($interfaceName, $methodName)
    {
        return $this->getMethodsMap($interfaceName)[$methodName]['type'];
    }

    /**
     * Convert array of custom_attributes to use flat array structure
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $customAttributes
     * @param string $dataObjectType
     * @return array
     */
    protected function convertCustomAttributes($customAttributes, $dataObjectType)
    {
        $result = [];
        foreach ((array)$customAttributes as $customAttribute) {
            $result[] = $this->convertCustomAttribute($customAttribute, $dataObjectType);
        }
        return $result;
    }

    /**
     * Convert custom_attribute object to use flat array structure
     *
     * @param \Magento\Framework\Api\AttributeInterface $customAttribute
     * @param string $dataObjectType
     * @return array
     */
    protected function convertCustomAttribute($customAttribute, $dataObjectType)
    {
        $data = [];
        $data[AttributeValue::ATTRIBUTE_CODE] = $customAttribute->getAttributeCode();
        $value = $customAttribute->getValue();
        if (is_object($value)) {
            $type = $this->attributeTypeResolver->resolveObjectType(
                $customAttribute->getAttributeCode(),
                $value,
                $dataObjectType
            );
            $value = $this->buildOutputDataArray($value, $type);
        } elseif (is_array($value)) {
            $valueResult = [];
            foreach ($value as $singleValue) {
                if (is_object($singleValue)) {
                    $type = $this->attributeTypeResolver->resolveObjectType(
                        $customAttribute->getAttributeCode(),
                        $singleValue,
                        $dataObjectType
                    );
                    $singleValue = $this->buildOutputDataArray($singleValue, $type);
                }
                // Cannot cast to a type because the type is unknown
                $valueResult[] = $singleValue;
            }
            $value = $valueResult;
        }
        $data[AttributeValue::VALUE] = $value;
        return $data;
    }

    /**
     * Return service interface or Data interface methods loaded from cache
     *
     * @param string $interfaceName
     * @return array
     * <pre>
     * Service methods' reflection data stored in cache as 'methodName' => 'returnType'
     * ex.
     * [
     *  'create' => '\Magento\Customer\Api\Data\Customer',
     *  'validatePassword' => 'boolean'
     * ]
     * </pre>
     */
    public function getMethodsMap($interfaceName)
    {
        $key = self::SERVICE_INTERFACE_METHODS_CACHE_PREFIX . "-" . md5($interfaceName);
        if (!isset($this->serviceInterfaceMethodsMap[$key])) {
            $methodMap = $this->cache->load($key);
            if ($methodMap) {
                $this->serviceInterfaceMethodsMap[$key] = unserialize($methodMap);
            } else {
                $methodMap = $this->getMethodMapViaReflection($interfaceName);
                $this->serviceInterfaceMethodsMap[$key] = $methodMap;
                $this->cache->save(serialize($this->serviceInterfaceMethodsMap[$key]), $key);
            }
        }
        return $this->serviceInterfaceMethodsMap[$key];
    }

    /**
     * Use reflection to load the method information
     *
     * @param string $interfaceName
     * @return array
     */
    protected function getMethodMapViaReflection($interfaceName)
    {
        $methodMap = [];
        $class = new ClassReflection($interfaceName);
        $baseClassMethods = false;
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // Include all the methods of classes inheriting from AbstractExtensibleObject.
            // Ignore all the methods of AbstractExtensibleModel's parent classes
            if ($method->class === self::BASE_MODEL_CLASS) {
                $baseClassMethods = true;
            } elseif ($baseClassMethods) {
                // ReflectionClass::getMethods() sorts the methods by class (lowest in inheritance tree first)
                // then by the order they are defined in the class definition
                break;
            }

            if ($this->isSuitableMethod($method)) {
                $methodMap[$method->getName()] = $this->typeProcessor->getGetterReturnType($method);
            }
        }
        return $methodMap;
    }

    /**
     * Determines if the method is suitable to be used by the processor.
     *
     * @param \ReflectionMethod $method
     * @return bool
     */
    protected function isSuitableMethod($method)
    {
        $isSuitableMethodType = !($method->isConstructor() || $method->isFinal()
            || $method->isStatic() || $method->isDestructor());

        $isExcludedMagicMethod = in_array(
            $method->getName(),
            ['__sleep', '__wakeup', '__clone']
        );
        return $isSuitableMethodType && !$isExcludedMagicMethod;
    }
}
