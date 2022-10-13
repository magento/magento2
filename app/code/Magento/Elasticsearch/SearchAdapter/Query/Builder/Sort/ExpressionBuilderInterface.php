<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
