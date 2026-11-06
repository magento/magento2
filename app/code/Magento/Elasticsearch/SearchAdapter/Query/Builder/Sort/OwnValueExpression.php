<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\OwnValueSortField;
use Magento\Framework\Search\RequestInterface;

/**
 * Sort a sortable source attribute by the product's own value instead of the values of its children.
 */
class OwnValueExpression implements ExpressionBuilderInterface
{
    /**
     * @var DefaultExpression
     */
    private $defaultExpression;

    /**
     * @param DefaultExpression $defaultExpression
     */
    public function __construct(DefaultExpression $defaultExpression)
    {
        $this->defaultExpression = $defaultExpression;
    }

    /**
     * @inheritdoc
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array
    {
        // Documents indexed before the own-value field existed have no value for it (and an index built before
        // that has no mapping), so the label sort that applied until then decides their order.
        return [
            OwnValueSortField::PREFIX . $attribute->getAttributeCode() => [
                'order' => $direction,
                'missing' => '_last',
                'unmapped_type' => 'keyword',
            ],
        ] + $this->defaultExpression->build($attribute, $direction, $request);
    }
}
