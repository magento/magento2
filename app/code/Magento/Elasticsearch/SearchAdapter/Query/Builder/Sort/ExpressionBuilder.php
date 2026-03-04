<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Search\RequestInterface;

class ExpressionBuilder implements ExpressionBuilderInterface
{
    /**
     * @var ExpressionBuilderInterface
     */
    private $defaultExpressionBuilder;

    /**
     * @var ExpressionBuilderInterface[]
     */
    private $customExpressionBuilders;

    /**
     * @param ExpressionBuilderInterface $defaultExpressionBuilder
     * @param ExpressionBuilderInterface[] $customExpressionBuilders
     */
    public function __construct(
        ExpressionBuilderInterface $defaultExpressionBuilder,
        array $customExpressionBuilders = []
    ) {
        $this->defaultExpressionBuilder = $defaultExpressionBuilder;
        $this->customExpressionBuilders = $customExpressionBuilders;
    }

    /**
     * @inheritdoc
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array
    {
        return isset($this->customExpressionBuilders[$attribute->getAttributeCode()])
            ? $this->customExpressionBuilders[$attribute->getAttributeCode()]->build($attribute, $direction, $request)
            : $this->defaultExpressionBuilder->build($attribute, $direction, $request);
    }
}
