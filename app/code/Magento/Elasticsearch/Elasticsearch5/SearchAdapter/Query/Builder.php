<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Query;

use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation as AggregationBuilder;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Query builder for search adapter.
 *
 * @api
 * @since 100.1.0
 */
class Builder
{
    /**
     * @var Config
     * @since 100.1.0
     */
    protected $clientConfig;

    /**
     * @var SearchIndexNameResolver
     * @since 100.1.0
     */
    protected $searchIndexNameResolver;

    /**
     * @var AggregationBuilder
     * @since 100.1.0
     */
    protected $aggregationBuilder;

    /**
     * @var ScopeResolverInterface
     * @since 100.1.0
     */
    protected $scopeResolver;

    /**
     * @var Sort
     */
    private $sortBuilder;

    /**
     * @param Config $clientConfig
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param AggregationBuilder $aggregationBuilder
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        Config $clientConfig,
        SearchIndexNameResolver $searchIndexNameResolver,
        AggregationBuilder $aggregationBuilder,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->clientConfig = $clientConfig;
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Set initial settings for query
     *
     * @param RequestInterface $request
     * @return array
     * @since 100.1.0
     */
    public function initQuery(RequestInterface $request)
    {
        $dimension = current($request->getDimensions());
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();

        $searchQuery = [
            'index' => $this->searchIndexNameResolver->getIndexName($storeId, $request->getIndex()),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'from' => $request->getFrom(),
                'size' => $request->getSize(),
                'stored_fields' => ['_id', '_score'],
                'sort' => $this->getSortBuilder()->getSort($request),
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
     * @since 100.1.0
     */
    public function initAggregations(
        RequestInterface $request,
        array $searchQuery
    ) {
        return $this->aggregationBuilder->build($request, $searchQuery);
    }

    /**
     * Get sort builder instance.
     *
     * @return Sort
     */
    private function getSortBuilder()
    {
        if (null === $this->sortBuilder) {
            $this->sortBuilder = ObjectManager::getInstance()->get(Sort::class);
        }
        return $this->sortBuilder;
    }
}
