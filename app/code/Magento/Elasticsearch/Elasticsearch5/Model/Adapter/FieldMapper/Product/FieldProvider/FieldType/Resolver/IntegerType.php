<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface;

/**
 * Integer type resolver.
 */
class IntegerType implements ResolverInterface
{
    /**
     * @var ConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @var array
     */
    private $integerTypeAttributes;

    /**
     * @param ConverterInterface $fieldTypeConverter
     * @param array $integerTypeAttributes
     */
    public function __construct(ConverterInterface $fieldTypeConverter, $integerTypeAttributes = ['category_ids'])
    {
        $this->fieldTypeConverter = $fieldTypeConverter;
        $this->integerTypeAttributes = $integerTypeAttributes;
    }

    /**
     * Get integer field type.
     *
     * @param AttributeAdapter $attribute
     * @return string
     */
    public function getFieldType(AttributeAdapter $attribute): ?string
    {
        if (in_array($attribute->getAttributeCode(), $this->integerTypeAttributes, true)
            || ($attribute->isIntegerType() || $attribute->isBooleanType())
        ) {
            return $this->fieldTypeConverter->convert(ConverterInterface::INTERNAL_DATA_TYPE_INT);
        }

        return null;
    }
}
