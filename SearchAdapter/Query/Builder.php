<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query;

use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation as AggregationBuilder;

class Builder
{
    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var SearchIndexNameResolver
     */
    protected $searchIndexNameResolver;

    /**
     * @var AggregationBuilder
     */
    protected $aggregationBuilder;

    /**
     * @param Config $clientConfig
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param AggregationBuilder $aggregationBuilder
     */
    public function __construct(
        Config $clientConfig,
        SearchIndexNameResolver $searchIndexNameResolver,
        AggregationBuilder $aggregationBuilder
    ) {
        $this->clientConfig = $clientConfig;
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    /**
     * Set initial settings for query
     *
     * @param RequestInterface $request
     * @return array
     */
    public function initQuery(RequestInterface $request)
    {
        $dimension = current($request->getDimensions());
        $storeId = $dimension->getValue();
        $searchQuery = [
            'index' => $this->searchIndexNameResolver->getIndexName($storeId, $request->getIndex()),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'from' => $request->getFrom(),
                'size' => $request->getSize(),
                'fields' => ['_id', '_score'],
                'query' => [],
            ],
        ];
        return $searchQuery;
    }

    /**
     * Add aggregations settings to query
     *
     * @param RequestInterface $request
     * @param array $searchQuery
     * @return array
     */
    public function initAggregations(
        RequestInterface $request,
        array $searchQuery
    ) {
        return $this->aggregationBuilder->build($request, $searchQuery);
    }
}
