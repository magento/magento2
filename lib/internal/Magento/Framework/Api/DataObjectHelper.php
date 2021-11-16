<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Reflection\MethodsMap;

/**
 * Service class allow populating object from array data
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _setDataValues($dataObject, array $data, $interfaceName)
    {
        if (empty($data)) {
            return $this;
        }
        $setMethods = $this->getSetters($dataObject);
        if ($dataObject instanceof ExtensibleDataInterface
            && !empty($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])
        ) {
            foreach ($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] as $customAttribute) {
                $dataObject->setCustomAttribute(
                    $customAttribute[AttributeInterface::ATTRIBUTE_CODE],
                    $customAttribute[AttributeInterface::VALUE]
                );
            }
            unset($data[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]);
        }
        if ($dataObject instanceof \Magento\Framework\Model\AbstractModel) {
            $simpleData = array_filter($data, static function ($e) {
                return is_scalar($e) || is_null($e);
            });
            if (isset($simpleData['id'])) {
                $dataObject->setId($simpleData['id']);
                unset($simpleData['id']);
            }
            $simpleData = array_intersect_key($simpleData, $setMethods);
            $dataObject->addData($simpleData);
            $data = array_diff_key($data, $simpleData);
            if (\count($data) === 0) {
                return $this;
            }
        }
        foreach (array_intersect_key($data, $setMethods) as $key => $value) {
            $methodName = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);

            if (!is_array($value)) {
                if ($methodName !== 'ExtensionAttributes' || $value !== null) {
                    if (method_exists($dataObject, 'set' . $methodName)) {
                        $dataObject->{'set' . $methodName}($value);
                    } else {
                        $dataObject->{'setIs' . $methodName}($value);
                    }
                }
            } else {
                $getterMethodName = 'get' . $methodName;
                $this->setComplexValue($dataObject, $getterMethodName, 'set' . $methodName, $value, $interfaceName);
            }
            unset($data[$key]);
        }

        if ($dataObject instanceof CustomAttributesDataInterface) {
            foreach ($data as $key => $value) {
                $dataObject->setCustomAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Set complex (like object) value using $methodName based on return type of $getterMethodName
     *
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
                    = 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase(
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

    /** @var array  */
    private array $settersCache = [];

    /**
     * Get list of setters for object
     *
     * @param object $dataObject
     * @return array
     */
    private function getSetters(object $dataObject): array
    {
        $class = get_class($dataObject);
        if (!isset($this->settersCache[$class])) {
            $dataObjectMethods = get_class_methods($class);
            // use regexp to manipulate with method list as it use jit starting with PHP 7.3
            $setters = array_filter(
                explode(
                    ',',
                    strtolower(
                        // (0) remove all not setter
                        // (1) add _ before upper letter
                        // (2) remove set_ in start of name
                        // (3) add name without is_ prefix
                        preg_replace(
                            ['/(^|,)(?!set)[^,]*/S','/(.)([A-Z])/S', '/(^|,)set_/iS', '/(^|,)is_([^,]+)/is'],
                            ['', '$1_$2', '$1', '$1$2,is_$2'],
                            implode(',', $dataObjectMethods)
                        )
                    )
                )
            );
            $this->settersCache[$class] = array_flip($setters);
        }
        return $this->settersCache[$class];
    }
}
