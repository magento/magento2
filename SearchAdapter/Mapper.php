<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match as MatchQueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder as FilterBuilder;

/**
 * Mapper class
 */
class Mapper
{
    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var MatchQueryBuilder
     */
    protected $matchQueryBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var AggregationBuilder
     */
    protected $aggregationBuilder;

    /**
     * @param Config $clientConfig
     * @param MatchQueryBuilder $matchQueryBuilder
     * @param FilterBuilder $filterBuilder
     * @param AggregationBuilder $aggregationBuilder
     */
    public function __construct(
        Config $clientConfig,
        MatchQueryBuilder $matchQueryBuilder,
        FilterBuilder $filterBuilder,
        AggregationBuilder $aggregationBuilder
    ) {
        $this->clientConfig = $clientConfig;
        $this->matchQueryBuilder = $matchQueryBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->aggregationBuilder = $aggregationBuilder;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @return array
     */
    public function buildQuery(RequestInterface $request)
    {
        $dimension = current($request->getDimensions());
        $storeId = $dimension->getValue();
        $searchQuery = [
            'index' => $this->clientConfig->getIndexName(),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'from' => $request->getFrom(),
                'size' => $request->getSize(),
                'fields' => ['_id', '_score'],
                'query' => $this->processQuery(
                    $request->getQuery(),
                    [],
                    BoolQuery::QUERY_CONDITION_MUST
                ),
            ],
        ];
        $searchQuery['body']['query']['bool']['minimum_should_match'] = 1;
        $searchQuery['body']['query']['bool']['must'][]= [
            'term' => [
                'store_id' => $storeId,
            ]
        ];
        $searchQuery = $this->aggregationBuilder->build($request, $searchQuery);
        return $searchQuery;
    }

    /**
     * Process query
     *
     * @param RequestQueryInterface $requestQuery
     * @param array $selectQuery
     * @param string $conditionType
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function processQuery(
        RequestQueryInterface $requestQuery,
        array $selectQuery,
        $conditionType
    ) {
        switch ($requestQuery->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQuery $requestQuery */
                $selectQuery = $this->matchQueryBuilder->build(
                    $selectQuery,
                    $requestQuery,
                    $conditionType
                );
                break;
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $requestQuery */
                $selectQuery = $this->processBoolQuery($requestQuery, $selectQuery);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $requestQuery */
                $selectQuery = $this->processFilterQuery($requestQuery, $selectQuery, $conditionType);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown query type \'%s\'', $requestQuery->getType()));
        }

        return $selectQuery;
    }

    /**
     * Process bool query
     *
     * @param BoolQuery $query
     * @param array $selectQuery
     * @return array
     */
    protected function processBoolQuery(
        BoolQuery $query,
        array $selectQuery
    ) {
        $selectQuery = $this->processBoolQueryCondition(
            $query->getMust(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_MUST
        );

        $selectQuery = $this->processBoolQueryCondition(
            $query->getShould(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_SHOULD
        );

        $selectQuery = $this->processBoolQueryCondition(
            $query->getMustNot(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_NOT
        );

        return $selectQuery;
    }

    /**
     * Process bool query condition (must, should, must_not)
     *
     * @param RequestQueryInterface[] $subQueryList
     * @param array $selectQuery
     * @param string $conditionType
     * @return array
     */
    protected function processBoolQueryCondition(
        array $subQueryList,
        array $selectQuery,
        $conditionType
    ) {
        foreach ($subQueryList as $subQuery) {
            $selectQuery = $this->processQuery($subQuery, $selectQuery, $conditionType);
        }

        return $selectQuery;
    }

    /**
     * Process filter query
     *
     * @param FilterQuery $query
     * @param array $selectQuery
     * @param string $conditionType
     * @return array
     */
    private function processFilterQuery(
        FilterQuery $query,
        array $selectQuery,
        $conditionType
    ) {
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $selectQuery = $this->processQuery($query->getReference(), $selectQuery, $conditionType);
                break;
            case FilterQuery::REFERENCE_FILTER:
                $selectQuery['bool']['must']= array_merge(
                    isset($selectQuery['bool']['must']) ? $selectQuery['bool']['must'] : [],
                    $this->filterBuilder->build($query->getReference(), $conditionType)
                );
                break;
        }

        return $selectQuery;
    }
}
