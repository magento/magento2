<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\Resource\Config;
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
     * @var \Magento\Framework\App\Resource
     */
    private $resource;

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
     * @param \Magento\Framework\App\Resource $resource
     * @param ScoreBuilderFactory $scoreBuilderFactory
     * @param MatchQueryBuilder $matchQueryBuilder
     * @param Builder $filterBuilder
     * @param Dimensions $dimensionsBuilder
     * @param ConditionManager $conditionManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        ScoreBuilderFactory $scoreBuilderFactory,
        MatchQueryBuilder $matchQueryBuilder,
        Builder $filterBuilder,
        Dimensions $dimensionsBuilder,
        ConditionManager $conditionManager
    ) {
        $this->resource = $resource;
        $this->scoreBuilderFactory = $scoreBuilderFactory;
        $this->matchQueryBuilder = $matchQueryBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->dimensionsBuilder = $dimensionsBuilder;
        $this->conditionManager = $conditionManager;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @return Select
     */
    public function buildQuery(RequestInterface $request)
    {
        /** @var ScoreBuilder $scoreBuilder */
        $scoreBuilder = $this->scoreBuilderFactory->create();
        $select = $this->processQuery(
            $scoreBuilder,
            $request->getQuery(),
            $this->getSelect(),
            BoolQuery::QUERY_CONDITION_MUST
        );
        $select = $this->processDimensions($request, $select);
        $tableName = $this->resource->getTableName($request->getIndex());
        $select->from($tableName)
            ->columns($scoreBuilder->build())
            ->order($scoreBuilder->getScoreAlias() . ' ' . Select::SQL_DESC);
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
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $scoreBuilder->startQuery();
                $select = $this->processQuery($scoreBuilder, $query->getReference(), $select, $conditionType);
                $scoreBuilder->endQuery($query->getBoost());
                break;
            case FilterQuery::REFERENCE_FILTER:
                $filterCondition = $this->filterBuilder->build($query->getReference(), $conditionType);
                $select->where($filterCondition);
                $scoreBuilder->addCondition(1, $query->getBoost());
                break;
        }
        return $select;
    }

    /**
     * Get empty Select
     *
     * @return Select
     */
    private function getSelect()
    {
        return $this->resource->getConnection(\Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE)->select();
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

        $query = $this->conditionManager->combineQueries($dimensions, \Zend_Db_Select::SQL_OR);
        if (!empty($query)) {
            $select->where($this->conditionManager->wrapBrackets($query));
        }

        return $select;
    }
}
