<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\Search\RequestInterface;

class DefaultExpression implements ExpressionBuilderInterface
{
    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @param FieldNameResolver $fieldNameResolver
     */
    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array
    {
        $fieldName = $this->fieldNameResolver->getFieldName($attribute);
        if ($attribute->isSortable() &&
            !$attribute->isComplexType() &&
            !($attribute->isFloatType() || $attribute->isIntegerType())
        ) {
            $suffix = $this->fieldNameResolver->getFieldName(
                $attribute,
                ['type' => FieldMapperInterface::TYPE_SORT]
            );
            $fieldName .= '.' . $suffix;
        }
        if ($attribute->isComplexType() && $attribute->isSortable()) {
            $fieldName .= '_value';
            $suffix = $this->fieldNameResolver->getFieldName(
                $attribute,
                ['type' => FieldMapperInterface::TYPE_SORT]
            );
            $fieldName .= '.' . $suffix;
        }

        return [
            $fieldName => ['order' => $direction],
        ];
    }
}
