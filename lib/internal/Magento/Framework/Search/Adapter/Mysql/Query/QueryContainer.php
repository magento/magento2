<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Adapter\Mysql\Query;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilderFactory;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;

class QueryContainer
{
    const DERIVED_QUERY_PREFIX = 'derived_';
    /**
     * @var array [[$select, $scoreBuilder], [$select, $scoreBuilder]]
     */
    private $queries = [];
    /**
     * @var ScoreBuilderFactory
     */
    private $scoreBuilderFactory;
    /**
     * @var Match
     */
    private $matchBuilder;
    /**
     * @var IndexBuilderInterface
     */
    private $indexBuilder;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string[]
     */
    private $filters = [];

    /**
     * @var int
     */
    private $filtersCount = 0;

    /**
     * @param ScoreBuilderFactory $scoreBuilderFactory
     * @param Match $matchBuilder
     * @param IndexBuilderInterface $indexBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        ScoreBuilderFactory $scoreBuilderFactory,
        Match $matchBuilder,
        IndexBuilderInterface $indexBuilder,
        RequestInterface $request
    ) {
        $this->scoreBuilderFactory = $scoreBuilderFactory;
        $this->matchBuilder = $matchBuilder;
        $this->indexBuilder = $indexBuilder;
        $this->request = $request;
    }

    /**
     * @param Select $select
     * @param RequestQueryInterface $query
     * @param string $conditionType
     * @return Select
     */
    public function addMatchQuery(
        Select $select,
        RequestQueryInterface $query,
        $conditionType
    ) {
        $subSelect = $this->createSelect();
        $subScoreBuilder = $this->scoreBuilderFactory->create();
        $this->buildMatchQuery($subScoreBuilder, $subSelect, $query, $conditionType);
        $subSelect->columns($subScoreBuilder->build());
        $subSelect->limit($this->request->getSize());
        $this->addDerivedQuery($subSelect);

        return $select;
    }

    /**
     * @param string $filter
     * @return void
     */
    public function addFilter($filter)
    {
        $this->filters[] = '(' . $filter . ')';
        $this->filtersCount++;
    }

    /**
     * @return void
     */
    public function clearFilters()
    {
        $this->filters = [];
    }

    /**
     * @return string[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return int
     */
    public function getFiltersCount()
    {
        return $this->filtersCount;
    }

    /**
     * @return Select[]
     */
    public function getDerivedQueries()
    {
        return $this->queries;
    }

    /**
     * @return array
     */
    public function getDerivedQueryNames()
    {
        return array_keys($this->getDerivedQueries());
    }

    /**
     * @param Select $select
     * @return void
     */
    private function addDerivedQuery(Select $select)
    {
        $name = self::DERIVED_QUERY_PREFIX . count($this->queries);
        $this->queries[$name] = $select;
    }

    /**
     * @return Select
     */
    private function createSelect()
    {
        return $this->indexBuilder->build($this->request);
    }

    /**
     * @param ScoreBuilder $scoreBuilder
     * @param Select $select
     * @param RequestQueryInterface $query
     * @param string $conditionType
     * @return Select
     */
    private function buildMatchQuery(
        ScoreBuilder $scoreBuilder,
        Select $select,
        RequestQueryInterface $query,
        $conditionType
    ) {
        $select = $this->matchBuilder->build($scoreBuilder, $select, $query, $conditionType);
        return $select;
    }
}
