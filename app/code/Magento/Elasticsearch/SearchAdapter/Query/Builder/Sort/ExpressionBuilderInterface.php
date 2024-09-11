<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Search\RequestInterface;

interface ExpressionBuilderInterface
{
    /**
     * Build sort expression for the provided attribute.
     *
     * @param AttributeAdapter $attribute
     * @param string $direction
     * @param RequestInterface $request
     * @return array
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array;
}
