<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Aggregation;

use Magento\Framework\Search\RequestInterface;

/**
 * Class \Magento\Framework\Search\Adapter\Aggregation\AggregationResolver
 *
 * @since 2.1.0
 */
class AggregationResolver implements AggregationResolverInterface
{
    /**
     * @var AggregationResolverInterface[]
     * @since 2.1.0
     */
    private $resolvers;

    /**
     * @param AggregationResolverInterface[] $resolvers
     * @since 2.1.0
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function resolve(RequestInterface $request, array $documentIds)
    {
        $aggregations = isset($this->resolvers[$request->getIndex()])
            ? $this->resolvers[$request->getIndex()]->resolve($request, $documentIds)
            : $request->getAggregation();
        return $aggregations;
    }
}
