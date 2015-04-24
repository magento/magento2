<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Phrase;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\CustomAttributesDataInterface;
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

    /**
     * @var \Magento\Framework\Api\AttributeTypeResolverInterface
     */
    protected $attributeTypeResolver;

    /**
     * @var MethodsMap
     */
    private $methodsMapProcessor;

    /**
     * @var ExtensionAttributesProcessor
     */
    private $extensionAttributesProcessor;

    /**
     * @var TypeCaster
     */
    private $typeCaster;

    /**
     * @param \Magento\Framework\Api\AttributeTypeResolverInterface $typeResolver
     * @param MethodsMap $methodsMapProcessor
     * @param ExtensionAttributesProcessor $extensionAttributesProcessor
     * @param TypeCaster $typeCaster
     */
    public function __construct(
        \Magento\Framework\Api\AttributeTypeResolverInterface $typeResolver,
        MethodsMap $methodsMapProcessor,
        ExtensionAttributesProcessor $extensionAttributesProcessor,
        TypeCaster $typeCaster
    ) {
        $this->attributeTypeResolver = $typeResolver;
        $this->methodsMapProcessor = $methodsMapProcessor;
        $this->extensionAttributesProcessor = $extensionAttributesProcessor;
        $this->typeCaster = $typeCaster;
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
        $methods = $this->methodsMapProcessor->getMethodsMap($dataObjectType);
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
                $outputData[$key] = $this->typeCaster->castValueToType($value, $returnType);
            } elseif (substr($methodName, 0, 3) === self::HAS_METHOD_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                $outputData[$key] = $this->typeCaster->castValueToType($value, $returnType);
            } elseif (substr($methodName, 0, 3) === self::GETTER_PREFIX) {
                $value = $dataObject->{$methodName}();
                if ($methodName === 'getCustomAttributes' && $value === []) {
                    continue;
                }
                if ($value === null && !$methodReflectionData['isRequired']) {
                    continue;
                }
                $key = SimpleDataObjectConverter::camelCaseToSnakeCase(substr($methodName, 3));
                if ($key === CustomAttributesDataInterface::CUSTOM_ATTRIBUTES) {
                    $value = $this->convertCustomAttributes($value, $dataObjectType);
                } elseif ($key === "extension_attributes") {
                    $value = $this->extensionAttributesProcessor->buildOutputDataArray($value, $returnType);
                } elseif (is_object($value) && !($value instanceof Phrase)) {
                    $value = $this->buildOutputDataArray($value, $returnType);
                } elseif (is_array($value)) {
                    $valueResult = [];
                    $arrayElementType = substr($returnType, 0, -2);
                    foreach ($value as $singleValue) {
                        if (is_object($singleValue) && !($singleValue instanceof Phrase)) {
                            $singleValue = $this->buildOutputDataArray($singleValue, $arrayElementType);
                        }
                        $valueResult[] = $this->typeCaster->castValueToType($singleValue, $arrayElementType);
                    }
                    $value = $valueResult;
                } else {
                    $value = $this->typeCaster->castValueToType($value, $returnType);
                }

                $outputData[$key] = $value;
            }
        }
        return $outputData;
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
}
