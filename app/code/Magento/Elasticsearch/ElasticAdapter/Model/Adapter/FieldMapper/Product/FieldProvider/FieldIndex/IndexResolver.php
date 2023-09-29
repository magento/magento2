<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\ElasticAdapter\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ResolverInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;

/**
 * Field index resolver that provides index type for the attribute in mapping.
 * For example, we need to set ‘no’/false in the case when attribute must be present in index data,
 * but stay as not indexable.
 */
class IndexResolver implements ResolverInterface
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var FieldTypeConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var FieldTypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @param ConverterInterface $converter
     * @param FieldTypeConverterInterface $fieldTypeConverter
     * @param FieldTypeResolver $fieldTypeResolver
     */
    public function __construct(
        ConverterInterface $converter,
        FieldTypeConverterInterface $fieldTypeConverter,
        FieldTypeResolver $fieldTypeResolver
    ) {
        $this->converter = $converter;
        $this->fieldTypeConverter = $fieldTypeConverter;
        $this->fieldTypeResolver = $fieldTypeResolver;
    }

    /**
     * @inheritdoc
     */
    public function getFieldIndex(AttributeAdapter $attribute)
    {
        $index = null;
        if (!$attribute->isSearchable()
            && !$attribute->isAlwaysIndexable()
            && ($this->isStringServiceFieldType($attribute) || $attribute->isComplexType())
            && !(($attribute->isIntegerType() || $attribute->isBooleanType())
                && !$attribute->isUserDefined())
            && !$attribute->isFloatType()
        ) {
            $index = $this->converter->convert(ConverterInterface::INTERNAL_NO_INDEX_VALUE);
        }

        return $index;
    }

    /**
     * Check if service field type for field set as 'string'
     *
     * @param AttributeAdapter $attribute
     * @return bool
     */
    private function isStringServiceFieldType(AttributeAdapter $attribute): bool
    {
        $serviceFieldType = $this->fieldTypeResolver->getFieldType($attribute);
        $stringTypeKey = $this->fieldTypeConverter->convert(FieldTypeConverterInterface::INTERNAL_DATA_TYPE_STRING);

        return $serviceFieldType === $stringTypeKey;
    }
}
