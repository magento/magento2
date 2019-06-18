<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;

/**
 * Default name resolver.
 */
class DefaultResolver implements ResolverInterface
{
    /**
     * @var FieldTypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @param FieldTypeResolver $fieldTypeResolver
     * @param FieldTypeConverterInterface $fieldTypeConverter
     */
    public function __construct(
        FieldTypeResolver $fieldTypeResolver,
        FieldTypeConverterInterface $fieldTypeConverter
    ) {
        $this->fieldTypeResolver = $fieldTypeResolver;
        $this->fieldTypeConverter = $fieldTypeConverter;
    }

    /**
     * Get field name.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        $attributeCode = $attribute->getAttributeCode();
        if (empty($context['type'])) {
            return $attributeCode;
        }

        $fieldType = $this->fieldTypeResolver->getFieldType($attribute);
        $frontendInput = $attribute->getFrontendInput();
        $fieldName = $attributeCode;
        if ($context['type'] === FieldMapperInterface::TYPE_FILTER) {
            if ($this->isStringServiceFieldType($fieldType)) {
                return $this->getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode);
            }
            $fieldName = $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_QUERY) {
            $fieldName = $this->getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode);
        } elseif ($context['type'] == FieldMapperInterface::TYPE_SORT && $attribute->isSortable()) {
            $fieldName = 'sort_' . $attributeCode;
        }

        return $fieldName;
    }

    /**
     * Check if service field type for field set as 'string'
     *
     * @param string $serviceFieldType
     * @return bool
     */
    private function isStringServiceFieldType(string $serviceFieldType): bool
    {
        $stringTypeKey = $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING);

        return $serviceFieldType === $stringTypeKey;
    }

    /**
     * Get field name for query type fields.
     *
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    private function getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode)
    {
        if ($attributeCode === '*') {
            $fieldName = '_all';
        } else {
            $fieldName = $this->getRefinedFieldName($frontendInput, $fieldType, $attributeCode);
        }
        return $fieldName;
    }

    /**
     * Prepare field name for complex fields.
     *
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    private function getRefinedFieldName($frontendInput, $fieldType, $attributeCode)
    {
        $stringTypeKey = $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING);
        $keywordTypeKey = $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_KEYWORD);
        switch ($frontendInput) {
            case 'select':
            case 'multiselect':
                return in_array($fieldType, [$stringTypeKey, $keywordTypeKey, 'integer'], true)
                    ? $attributeCode . '_value'
                    : $attributeCode;
            case 'boolean':
                return $fieldType === 'integer' ? $attributeCode . '_value' : $attributeCode;
            default:
                return $attributeCode;
        }
    }
}
