<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

use Magento\Framework\Phrase;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Zend\Code\Reflection\MethodReflection;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\AttributeTypeResolverInterface;

/**
 * Processes custom attributes and produces an array for the data.
 */
class CustomAttributesProcessor
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var AttributeTypeResolverInterface
     */
    private $attributeTypeResolver;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param AttributeTypeResolverInterface $typeResolver
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        AttributeTypeResolverInterface $typeResolver
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->attributeTypeResolver = $typeResolver;
    }

    /**
     * Writes out the custom attributes for a given object into an array.
     *
     * @param CustomAttributesDataInterface $objectWithCustomAttributes
     * @param string $dataObjectType
     * @return array
     */
    public function buildOutputDataArray(CustomAttributesDataInterface $objectWithCustomAttributes, $dataObjectType)
    {
        $customAttributes = $objectWithCustomAttributes->getCustomAttributes();
        $result = [];
        foreach ($customAttributes as $customAttribute) {
            $result[] = $this->convertCustomAttribute($customAttribute, $dataObjectType);
        }
        return $result;
    }

    /**
     * Convert custom_attribute object to use flat array structure
     *
     * @param AttributeInterface $customAttribute
     * @param string $dataObjectType
     * @return array
     */
    private function convertCustomAttribute(AttributeInterface $customAttribute, $dataObjectType)
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
            $value = $this->dataObjectProcessor->buildOutputDataArray($value, $type);
        } elseif (is_array($value)) {
            $valueResult = [];
            foreach ($value as $singleValue) {
                if (is_object($singleValue)) {
                    $type = $this->attributeTypeResolver->resolveObjectType(
                        $customAttribute->getAttributeCode(),
                        $singleValue,
                        $dataObjectType
                    );
                    $singleValue = $this->dataObjectProcessor->buildOutputDataArray($singleValue, $type);
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
