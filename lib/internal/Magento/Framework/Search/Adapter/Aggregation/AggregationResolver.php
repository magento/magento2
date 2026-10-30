<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Search\Adapter\Aggregation;

use Magento\Framework\Search\RequestInterface;

class AggregationResolver implements AggregationResolverInterface
{
    /**
     * @var AggregationResolverInterface[]
     */
    private $resolvers;

    /**
     * @param AggregationResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(RequestInterface $request, array $documentIds)
    {
        $aggregations = isset($this->resolvers[$request->getIndex()])
            ? $this->resolvers[$request->getIndex()]->resolve($request, $documentIds)
            : $request->getAggregation();
        return $aggregations;
    }
}
