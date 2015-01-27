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
     * Cached custom attribute codes by metadataServiceInterface
     *
     * @var array
     */
    protected $customAttributesCodes = [];

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $objectProcessor;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $typeProcessor;

    /**
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     */
    public function __construct(
        ObjectFactory $objectFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor
    ) {
        $this->objectFactory = $objectFactory;
        $this->objectProcessor = $objectProcessor;
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * @param ExtensibleDataInterface $dataObject
     * @param array $data
     * @return $this
     */
    public function populateWithArray(ExtensibleDataInterface $dataObject, array $data)
    {
        $this->_setDataValues($dataObject, $data);
        return $this;
    }

    /**
     * Template method used to configure the attribute codes for the custom attributes
     *
     * @param ExtensibleDataInterface $dataObject
     * @return string[]
     */
    public function getCustomAttributesCodes(ExtensibleDataInterface $dataObject)
    {
        $metadataServiceInterface = $dataObject->getMetadataServiceInterface();
        if (is_null($metadataServiceInterface)) {
            return [];
        }
        if (isset($this->customAttributesCodes[$metadataServiceInterface])) {
            return $this->customAttributesCodes[$metadataServiceInterface];
        }
        /** @var MetadataServiceInterface $metadataService */
        $metadataService = $this->objectFactory->get($metadataServiceInterface);
        $attributeCodes = [];
        /** @var \Magento\Framework\Api\MetadataObjectInterface[] $customAttributesMetadata */
        $customAttributesMetadata = $metadataService
            ->getCustomAttributesMetadata(get_class($dataObject));
        if (is_array($customAttributesMetadata)) {
            foreach ($customAttributesMetadata as $attribute) {
                $attributeCodes[] = $attribute->getAttributeCode();
            }
        }
        $this->customAttributesCodes[$metadataServiceInterface] = $attributeCodes;
        return $attributeCodes;
    }

    /**
     * Update Data Object with the data from array
     *
     * @param ExtensibleDataInterface $dataObject
     * @param array $data
     * @return $this
     */
    protected function _setDataValues(ExtensibleDataInterface $dataObject, array $data)
    {
        $dataObjectMethods = get_class_methods(get_class($dataObject));
        foreach ($data as $key => $value) {
            /* First, verify is there any setter for the key on the Service Data Object */
            $camelCaseKey = \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
            $possibleMethods = [
                'set' . $camelCaseKey,
                'setIs' . $camelCaseKey,
            ];
            if ($key === ExtensibleDataInterface::CUSTOM_ATTRIBUTES
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
                    $dataObject->$methodName($value);
                } else {
                    $getterMethodName = 'get' . $camelCaseKey;
                    $this->setComplexValue($dataObject, $getterMethodName, $methodName, $value);
                }
            } elseif (in_array($key, $this->getCustomAttributesCodes($dataObject))) {
                $dataObject->setCustomAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * @param ExtensibleDataInterface $dataObject
     * @param string $getterMethodName
     * @param string $methodName
     * @param array $value
     * @return $this
     */
    protected function setComplexValue(
        ExtensibleDataInterface $dataObject,
        $getterMethodName,
        $methodName,
        array $value
    ) {
        $returnType = $this->objectProcessor->getMethodReturnType(get_class($dataObject), $getterMethodName);
        if ($this->typeProcessor->isTypeSimple($returnType)) {
            $dataObject->$methodName($value);
            return $this;
        }

        if ($this->typeProcessor->isArrayType($returnType)) {
            $type = $this->typeProcessor->getArrayItemType($returnType);
            $objects = [];
            foreach ($value as $arrayElementData) {
                $object = $this->objectFactory->create($type, []);
                $this->populateWithArray($object, $arrayElementData);
                $objects[] = $object;
            }
            $dataObject->$methodName($objects);
            return $this;
        }

        if (is_subclass_of($returnType, '\Magento\Framework\Api\ExtensibleDataInterface')) {
            $object = $this->objectFactory->create($returnType, []);
            $this->populateWithArray($object, $value);
        } else {
            $object = $this->objectFactory->create($returnType, $value);
        }
        $dataObject->$methodName($object);
        return $this;
    }
}
