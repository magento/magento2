<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\ElasticAdapter\SearchAdapter;

use InvalidArgumentException;
use Magento\Elasticsearch\ElasticAdapter\SearchAdapter\Query\Builder as QueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder as FilterBuilder;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\MatchQuery as MatchQueryBuilder;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Mapper class for ElasticAdapter
 *
 * @api
 * @since 100.2.2
 */
class Mapper
{
    /**
     * @var QueryBuilder
     * @since 100.2.2
     */
    protected $queryBuilder;

    /**
     * @var MatchQueryBuilder
     * @since 100.2.2
     */
    protected $matchQueryBuilder;

    /**
     * @var FilterBuilder
     * @since 100.2.2
     */
    protected $filterBuilder;

    /**
     * @param QueryBuilder $queryBuilder
     * @param MatchQueryBuilder $matchQueryBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        MatchQueryBuilder $matchQueryBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->matchQueryBuilder = $matchQueryBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @return array
     * @since 100.2.2
     */
    public function buildQuery(RequestInterface $request)
    {
        $searchQuery = $this->queryBuilder->initQuery($request);
        $searchQuery['body']['query'] = array_merge(
            $searchQuery['body']['query'],
            $this->processQuery(
                $request->getQuery(),
                [],
                BoolQuery::QUERY_CONDITION_MUST
            )
        );

        if (isset($searchQuery['body']['query']['bool']['should'])) {
            $searchQuery['body']['query']['bool']['minimum_should_match'] = 1;
        }

        return $this->queryBuilder->initAggregations($request, $searchQuery);
    }

    /**
     * Process query
     *
     * @param RequestQueryInterface $requestQuery
     * @param array $selectQuery
     * @param string $conditionType
     * @return array
     * @throws InvalidArgumentException
     * @since 100.2.2
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
                throw new InvalidArgumentException(sprintf(
                    'Unknown query type \'%s\'',
                    $requestQuery->getType()
                ));
        }

        return $selectQuery;
    }

    /**
     * Process bool query
     *
     * @param BoolQuery $query
     * @param array $selectQuery
     * @return array
     * @since 100.2.2
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
     * @since 100.2.2
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
                $conditionType = $conditionType === BoolQuery::QUERY_CONDITION_NOT ?
                    MatchQueryBuilder::QUERY_CONDITION_MUST_NOT : $conditionType;
                $filterQuery = $this->filterBuilder->build($query->getReference(), $conditionType);
                foreach ($filterQuery['bool'] as $condition => $filter) {
                    //phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $selectQuery['bool'][$condition] = array_merge(
                        $selectQuery['bool'][$condition] ?? [],
                        $filter
                    );
                }
                break;
        }

        return $selectQuery;
    }
}
