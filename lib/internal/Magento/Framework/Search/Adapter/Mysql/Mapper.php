<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match as MatchQueryBuilder;
use Magento\Framework\Search\Request\Query\Bool as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Mapper class. Maps library request to specific adapter dependent query
 */
class Mapper
{
    /**
     * @var ScoreBuilder
     */
    private $scoreBuilderFactory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match
     */
    private $matchQueryBuilder;

    /**
     * @var Filter\Builder
     */
    private $filterBuilder;

    /**
     * @var Dimensions
     */
    private $dimensionsBuilder;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var array
     */
    private $indexProviders;

    /**
     * @param ScoreBuilderFactory $scoreBuilderFactory
     * @param MatchQueryBuilder $matchQueryBuilder
     * @param Builder $filterBuilder
     * @param Dimensions $dimensionsBuilder
     * @param ConditionManager $conditionManager
     * @param \Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface[] $indexProviders
     */
    public function __construct(
        ScoreBuilderFactory $scoreBuilderFactory,
        MatchQueryBuilder $matchQueryBuilder,
        Builder $filterBuilder,
        Dimensions $dimensionsBuilder,
        ConditionManager $conditionManager,
        array $indexProviders
    ) {
        $this->scoreBuilderFactory = $scoreBuilderFactory;
        $this->matchQueryBuilder = $matchQueryBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->dimensionsBuilder = $dimensionsBuilder;
        $this->conditionManager = $conditionManager;
        $this->indexProviders = $indexProviders;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @throws \Exception
     * @return Select
     */
    public function buildQuery(RequestInterface $request)
    {
        if (!isset($this->indexProviders[$request->getIndex()])) {
            throw new \Exception('Index provider not configured');
        }
        $select = $this->indexProviders[$request->getIndex()]->build($request);

        /** @var ScoreBuilder $scoreBuilder */
        $scoreBuilder = $this->scoreBuilderFactory->create();
        $select = $this->processQuery(
            $scoreBuilder,
            $request->getQuery(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST
        );
        $select = $this->processDimensions($request, $select);
        $select->columns($scoreBuilder->build());
        $select->order($scoreBuilder->getScoreAlias() . ' ' . Select::SQL_DESC);
        return $select;
    }

    /**
     * Process query
     *
     * @param ScoreBuilder $scoreBuilder
     * @param RequestQueryInterface $query
     * @param Select $select
     * @param string $conditionType
     * @return Select
     * @throws \InvalidArgumentException
     */
    protected function processQuery(
        ScoreBuilder $scoreBuilder,
        RequestQueryInterface $query,
        Select $select,
        $conditionType
    ) {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQuery $query */
                $select = $this->matchQueryBuilder->build(
                    $scoreBuilder,
                    $select,
                    $query,
                    $conditionType
                );
                break;
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $query */
                $select = $this->processBoolQuery($scoreBuilder, $query, $select);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $query */
                $select = $this->processFilterQuery($scoreBuilder, $query, $select, $conditionType);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown query type \'%s\'', $query->getType()));
        }
        return $select;
    }

    /**
     * Process bool query
     *
     * @param ScoreBuilder $scoreBuilder
     * @param BoolQuery $query
     * @param Select $select
     * @return Select
     */
    private function processBoolQuery(ScoreBuilder $scoreBuilder, BoolQuery $query, Select $select)
    {
        $scoreBuilder->startQuery();

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMust(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getShould(),
            $select,
            BoolQuery::QUERY_CONDITION_SHOULD
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMustNot(),
            $select,
            BoolQuery::QUERY_CONDITION_NOT
        );

        $scoreBuilder->endQuery($query->getBoost());

        return $select;
    }

    /**
     * Process bool query condition (must, should, must_not)
     *
     * @param ScoreBuilder $scoreBuilder
     * @param RequestQueryInterface[] $subQueryList
     * @param Select $select
     * @param string $conditionType
     * @return Select
     */
    private function processBoolQueryCondition(
        ScoreBuilder $scoreBuilder,
        array $subQueryList,
        Select $select,
        $conditionType
    ) {
        foreach ($subQueryList as $subQuery) {
            $select = $this->processQuery($scoreBuilder, $subQuery, $select, $conditionType);
        }
        return $select;
    }

    /**
     * Process filter query
     *
     * @param ScoreBuilder $scoreBuilder
     * @param FilterQuery $query
     * @param Select $select
     * @param string $conditionType
     * @return Select
     */
    private function processFilterQuery(ScoreBuilder $scoreBuilder, FilterQuery $query, Select $select, $conditionType)
    {
        $scoreBuilder->startQuery();
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $select = $this->processQuery($scoreBuilder, $query->getReference(), $select, $conditionType);
                $scoreBuilder->endQuery($query->getBoost());
                break;
            case FilterQuery::REFERENCE_FILTER:
                $filterCondition = $this->filterBuilder->build($query->getReference(), $conditionType);
                $select->where($filterCondition);
                break;
        }
        $scoreBuilder->endQuery($query->getBoost());
        return $select;
    }

    /**
     * Add filtering by dimensions
     *
     * @param RequestInterface $request
     * @param Select $select
     * @return \Magento\Framework\DB\Select
     */
    private function processDimensions(RequestInterface $request, Select $select)
    {
        $dimensions = [];
        foreach ($request->getDimensions() as $dimension) {
            $dimensions[] = $this->dimensionsBuilder->build($dimension);
        }

        $query = $this->conditionManager->combineQueries($dimensions, Select::SQL_OR);
        if (!empty($query)) {
            $select->where($this->conditionManager->wrapBrackets($query));
        }

        return $select;
    }
}
