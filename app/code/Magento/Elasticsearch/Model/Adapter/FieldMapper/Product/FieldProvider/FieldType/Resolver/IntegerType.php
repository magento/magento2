<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

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
     * @param ConverterInterface $fieldTypeConverter
     */
    public function __construct(ConverterInterface $fieldTypeConverter)
    {
        $this->fieldTypeConverter = $fieldTypeConverter;
    }

    /**
     * Get integer field type.
     *
     * @param AttributeAdapter $attribute
     * @return string
     */
    public function getFieldType(AttributeAdapter $attribute): ?string
    {
        if (($attribute->isIntegerType() || $attribute->isBooleanType())
                && !$attribute->isUserDefined()
        ) {
            return $this->fieldTypeConverter->convert(ConverterInterface::INTERNAL_DATA_TYPE_INT);
        }

        return null;
    }
}
