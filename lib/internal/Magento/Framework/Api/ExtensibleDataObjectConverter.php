<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class to convert Extensible Data Object array to flat array
 */
class ExtensibleDataObjectConverter
{
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(DataObjectProcessor $dataObjectProcessor)
    {
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Convert AbstractExtensibleObject into a nested array.
     *
     * @param ExtensibleDataInterface $dataObject
     * @param string[] $skipAttributes
     * @param string $dataObjectType
     * @return array
     */
    public function toNestedArray(
        ExtensibleDataInterface $dataObject,
        $skipAttributes = [],
        $dataObjectType = null
    ) {
        if ($dataObjectType == null) {
            $dataObjectType = get_class($dataObject);
        }
        $dataObjectArray = $this->dataObjectProcessor->buildOutputDataArray($dataObject, $dataObjectType);
        //process custom attributes if present
        $dataObjectArray = $this->processCustomAttributes($dataObjectArray, $skipAttributes);

        if (!empty($dataObjectArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
            /** @var array $extensionAttributes */
            $extensionAttributes = $dataObjectArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY];
            unset($dataObjectArray[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
            foreach ($extensionAttributes as $attributeKey => $attributeValue) {
                if (!in_array($attributeKey, $skipAttributes)) {
                    $dataObjectArray[$attributeKey] = $attributeValue;
                }
            }
        }
        return $dataObjectArray;
    }

    /**
     * Recursive process array to process customer attributes
     *
     * @param array $dataObjectArray
     * @param array $skipAttributes
     * @return array
     */
    private function processCustomAttributes(array $dataObjectArray, array $skipAttributes): array
    {
        if (!empty($dataObjectArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY])) {
            /** @var AttributeValue[] $customAttributes */
            $customAttributes = $dataObjectArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY];
            unset($dataObjectArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY]);
            foreach ($customAttributes as $attributeValue) {
                if (!in_array($attributeValue[AttributeValue::ATTRIBUTE_CODE], $skipAttributes)) {
                    $dataObjectArray[$attributeValue[AttributeValue::ATTRIBUTE_CODE]]
                        = $attributeValue[AttributeValue::VALUE];
                }
            }
        }
        foreach ($dataObjectArray as $key => $value) {
            if (is_array($value)) {
                $dataObjectArray[$key] = $this->processCustomAttributes($value, $skipAttributes);
            }
        }
        return $dataObjectArray;
    }

    /**
     * Convert AbstractExtensibleObject into flat array.
     *
     * @param ExtensibleDataInterface $dataObject
     * @param string[] $skipCustomAttributes
     * @param string $dataObjectType
     * @return array
     */
    public function toFlatArray(
        ExtensibleDataInterface $dataObject,
        $skipCustomAttributes = [],
        $dataObjectType = null
    ) {
        $dataObjectArray = $this->toNestedArray($dataObject, $skipCustomAttributes, $dataObjectType);
        return ConvertArray::toFlatArray($dataObjectArray);
    }

    /**
     * Convert Extensible Data Object custom attributes in sequential array format.
     *
     * @param array $extensibleObjectData
     * @return array
     */
    public static function convertCustomAttributesToSequentialArray($extensibleObjectData)
    {
        $extensibleObjectData[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY] = array_values(
            $extensibleObjectData[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY]
        );
        return $extensibleObjectData;
    }
}
