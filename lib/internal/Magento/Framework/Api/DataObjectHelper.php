<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Reflection\MethodsMap;

/**
 * Data object helper.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataObjectHelper
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $objectProcessor;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $joinProcessor;

    /**
     * @var MethodsMap
     */
    protected $methodsMapProcessor;

    /**
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param MethodsMap $methodsMapProcessor
     */
    public function __construct(
        ObjectFactory $objectFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        MethodsMap $methodsMapProcessor
    ) {
        $this->objectFactory = $objectFactory;
        $this->objectProcessor = $objectProcessor;
        $this->typeProcessor = $typeProcessor;
        $this->extensionFactory = $extensionFactory;
        $this->joinProcessor = $joinProcessor;
        $this->methodsMapProcessor = $methodsMapProcessor;
    }

    /**
     * Populate data object using data in array format.
     *
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return $this
     */
    public function populateWithArray($dataObject, array $data, $interfaceName)
    {
        if ($dataObject instanceof ExtensibleDataInterface) {
            $data = $this->joinProcessor->extractExtensionAttributes(get_class($dataObject), $data);
        }
        $this->_setDataValues($dataObject, $data, $interfaceName);
        return $this;
    }

    /**
     * Update Data Object with the data from array
     *
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _setDataValues($dataObject, array $data, $interfaceName)
    {
        $dataObjectMethods = get_class_methods(get_class($dataObject));
        foreach ($data as $key => $value) {
            /* First, verify is there any setter for the key on the Service Data Object */
            $camelCaseKey = \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
            $possibleMethods = [
                'set' . $camelCaseKey,
                'setIs' . $camelCaseKey,
            ];
            if ($key === CustomAttributesDataInterface::CUSTOM_ATTRIBUTES
                && ($dataObject instanceof ExtensibleDataInterface)
                && is_array($data[$key])
                && !empty($data[$key])
            ) {
                foreach ($data[$key] as $customAttribute) {
                    $dataObject->setCustomAttribute(
                        $customAttribute[AttributeInterface::ATTRIBUTE_CODE],
                        $customAttribute[AttributeInterface::VALUE]
                    );
                }
            } elseif ($methodNames = array_intersect($possibleMethods, $dataObjectMethods)) {
                $methodName = array_values($methodNames)[0];
                if (!is_array($value)) {
                    if ($methodName === 'setExtensionAttributes' && $value === null) {
                        // Cannot pass a null value to a method with a typed parameter
                    } else {
                        $dataObject->$methodName($value);
                    }
                } else {
                    $getterMethodName = 'get' . $camelCaseKey;
                    $this->setComplexValue($dataObject, $getterMethodName, $methodName, $value, $interfaceName);
                }
            } elseif ($dataObject instanceof CustomAttributesDataInterface) {
                $dataObject->setCustomAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param mixed $dataObject
     * @param string $getterMethodName
     * @param string $methodName
     * @param array $value
     * @param string $interfaceName
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setComplexValue(
        $dataObject,
        $getterMethodName,
        $methodName,
        array $value,
        $interfaceName
    ) {
        if ($interfaceName == null) {
            $interfaceName = get_class($dataObject);
        }
        $returnType = $this->methodsMapProcessor->getMethodReturnType($interfaceName, $getterMethodName);
        if ($this->typeProcessor->isTypeSimple($returnType)) {
            $dataObject->$methodName($value);
            return $this;
        }

        if ($this->typeProcessor->isArrayType($returnType)) {
            $type = $this->typeProcessor->getArrayItemType($returnType);
            $objects = [];
            foreach ($value as $arrayElementData) {
                $object = $this->objectFactory->create($type, []);
                $this->populateWithArray($object, $arrayElementData, $type);
                $objects[] = $object;
            }
            $dataObject->$methodName($objects);
            return $this;
        }

        if (is_subclass_of($returnType, \Magento\Framework\Api\ExtensibleDataInterface::class)) {
            $object = $this->objectFactory->create($returnType, []);
            $this->populateWithArray($object, $value, $returnType);
        } elseif (is_subclass_of($returnType, \Magento\Framework\Api\ExtensionAttributesInterface::class)) {
            foreach ($value as $extensionAttributeKey => $extensionAttributeValue) {
                $extensionAttributeGetterMethodName
                    = 'get' . \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase(
                        $extensionAttributeKey
                    );
                $methodReturnType = $this->methodsMapProcessor->getMethodReturnType(
                    $returnType,
                    $extensionAttributeGetterMethodName
                );
                $extensionAttributeType = $this->typeProcessor->isArrayType($methodReturnType)
                    ? $this->typeProcessor->getArrayItemType($methodReturnType)
                    : $methodReturnType;
                if ($this->typeProcessor->isTypeSimple($extensionAttributeType)) {
                    $value[$extensionAttributeKey] = $extensionAttributeValue;
                } else {
                    if ($this->typeProcessor->isArrayType($methodReturnType)) {
                        foreach ($extensionAttributeValue as $key => $extensionAttributeArrayValue) {
                            $extensionAttribute = $this->objectFactory->create($extensionAttributeType, []);
                            $this->populateWithArray(
                                $extensionAttribute,
                                $extensionAttributeArrayValue,
                                $extensionAttributeType
                            );
                            $value[$extensionAttributeKey][$key] = $extensionAttribute;
                        }
                    } else {
                        $value[$extensionAttributeKey] = $this->objectFactory->create(
                            $extensionAttributeType,
                            ['data' => $extensionAttributeValue]
                        );
                    }
                }
            }
            $object = $this->extensionFactory->create(get_class($dataObject), ['data' => $value]);
        } else {
            $object = $this->objectFactory->create($returnType, $value);
        }
        $dataObject->$methodName($object);
        return $this;
    }

    /**
     * Merges second object onto the first
     *
     * @param string $interfaceName
     * @param mixed $firstDataObject
     * @param mixed $secondDataObject
     * @return $this
     * @throws \LogicException
     */
    public function mergeDataObjects(
        $interfaceName,
        $firstDataObject,
        $secondDataObject
    ) {
        if (!$firstDataObject instanceof $interfaceName || !$secondDataObject instanceof $interfaceName) {
            throw new \LogicException('Wrong prototype object given. It can only be of "' . $interfaceName . '" type.');
        }
        $secondObjectArray = $this->objectProcessor->buildOutputDataArray($secondDataObject, $interfaceName);
        $this->_setDataValues($firstDataObject, $secondObjectArray, $interfaceName);
        return $this;
    }

    /**
     * Filter attribute value objects for a provided data interface type from an array of custom attribute value objects
     *
     * @param AttributeValue[] $attributeValues Array of custom attribute
     * @param string $type Data interface type
     * @return AttributeValue[]
     */
    public function getCustomAttributeValueByType(array $attributeValues, $type)
    {
        $attributeValueArray = [];
        if (empty($attributeValues)) {
            return $attributeValueArray;
        }
        foreach ($attributeValues as $attributeValue) {
            if ($attributeValue->getValue() instanceof $type) {
                $attributeValueArray[] = $attributeValue;
            }
        }
        return $attributeValueArray;
    }
}
