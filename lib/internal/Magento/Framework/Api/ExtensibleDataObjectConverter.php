<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param string[] $skipCustomAttributes
     * @param string $dataObjectType
     * @return array
     */
    public function toNestedArray(
        ExtensibleDataInterface $dataObject,
        $skipCustomAttributes = [],
        $dataObjectType = null
    ) {
        if ($dataObjectType == null) {
            $dataObjectType = get_class($dataObject);
        }
        $dataObjectArray = $this->dataObjectProcessor->buildOutputDataArray($dataObject, $dataObjectType);
        //process custom attributes if present
        if (!empty($dataObjectArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY])) {
            /** @var AttributeValue[] $customAttributes */
            $customAttributes = $dataObjectArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY];
            unset ($dataObjectArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY]);
            foreach ($customAttributes as $attributeValue) {
                if (!in_array($attributeValue[AttributeValue::ATTRIBUTE_CODE], $skipCustomAttributes)) {
                    $dataObjectArray[$attributeValue[AttributeValue::ATTRIBUTE_CODE]]
                        = $attributeValue[AttributeValue::VALUE];
                }
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
