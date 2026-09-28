<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Search\RequestInterface;

class Relevance implements ExpressionBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array
    {
        return [
            '_score' => ['order' => $direction],
        ];
    }
}
