<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

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
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     */
    public function __construct(
        ObjectFactory $objectFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->objectProcessor = $objectProcessor;
        $this->typeProcessor = $typeProcessor;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return $this
     */
    public function populateWithArray($dataObject, array $data, $interfaceName)
    {
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
            } else {
                if ($dataObject instanceof ExtensibleDataInterface) {
                    $dataObject->setCustomAttribute($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * @param ExtensibleDataInterface $dataObject
     * @param string $getterMethodName
     * @param string $methodName
     * @param array $value
     * @param string $interfaceName
     * @return $this
     */
    protected function setComplexValue(
        ExtensibleDataInterface $dataObject,
        $getterMethodName,
        $methodName,
        array $value,
        $interfaceName
    ) {
        if ($interfaceName == null) {
            $interfaceName = get_class($dataObject);
        }
        $returnType = $this->objectProcessor->getMethodReturnType($interfaceName, $getterMethodName);
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

        if (is_subclass_of($returnType, '\Magento\Framework\Api\ExtensibleDataInterface')) {
            $object = $this->objectFactory->create($returnType, []);
            $this->populateWithArray($object, $value, $returnType);
        } else if (is_subclass_of($returnType, '\Magento\Framework\Api\ExtensionAttributesInterface')) {
            $object = $this->extensionFactory->create(get_class($dataObject), $value);
        } else {
            $object = $this->objectFactory->create($returnType, $value);
        }
        $dataObject->$methodName($object);
        return $this;
    }

    /**
     * Merges second object onto the first
     *
     * @param string                  $interfaceName
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
}
