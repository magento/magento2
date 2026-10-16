<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\App\ObjectManager;
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
     * @var OwnValueExpression|null
     */
    private $ownValueExpressionBuilder;

    /**
     * @param ExpressionBuilderInterface $defaultExpressionBuilder
     * @param ExpressionBuilderInterface[] $customExpressionBuilders
     * @param OwnValueExpression|null $ownValueExpressionBuilder
     */
    public function __construct(
        ExpressionBuilderInterface $defaultExpressionBuilder,
        array $customExpressionBuilders = [],
        ?OwnValueExpression $ownValueExpressionBuilder = null
    ) {
        $this->defaultExpressionBuilder = $defaultExpressionBuilder;
        $this->customExpressionBuilders = $customExpressionBuilders;
        $this->ownValueExpressionBuilder = $ownValueExpressionBuilder;
    }

    /**
     * @inheritdoc
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array
    {
        if (isset($this->customExpressionBuilders[$attribute->getAttributeCode()])) {
            return $this->customExpressionBuilders[$attribute->getAttributeCode()]
                ->build($attribute, $direction, $request);
        }

        return $attribute->isSortable() && $attribute->isComplexType()
            ? $this->getOwnValueExpressionBuilder()->build($attribute, $direction, $request)
            : $this->defaultExpressionBuilder->build($attribute, $direction, $request);
    }

    /**
     * Get the builder that sorts source attributes by the product's own value.
     *
     * @return OwnValueExpression
     */
    private function getOwnValueExpressionBuilder(): OwnValueExpression
    {
        return $this->ownValueExpressionBuilder ??= ObjectManager::getInstance()->get(OwnValueExpression::class);
    }
}
