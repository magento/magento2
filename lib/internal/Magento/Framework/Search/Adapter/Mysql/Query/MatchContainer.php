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
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\QueryInterface as BuilderQueryInterface;

class MatchContainer implements BuilderQueryInterface
{
    const QUERY_NAME_PREFIX = 'derivative_';
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
     * @var bool
     */
    private $hasMatches = false;
    /**
     * @var IndexBuilderInterface
     */
    private $indexBuilder;
    /**
     * @var RequestInterface
     */
    private $request;

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
     * @param ScoreBuilder $scoreBuilder
     * @param Select $select
     * @param RequestQueryInterface $query
     * @param string $conditionType
     * @return Select
     */
    public function build(
        ScoreBuilder $scoreBuilder,
        Select $select,
        RequestQueryInterface $query,
        $conditionType
    ) {
        if ($this->hasMatches) {
            $subSelect = $this->createSelect();
            $subScoreBuilder = $this->scoreBuilderFactory->create();
            $this->buildMatchQuery($subScoreBuilder, $subSelect, $query, $conditionType);
            $subSelect->columns($subScoreBuilder->build());
            $subSelect->limit($this->request->getSize());
            $this->addQuery($subSelect);
        } else {
            $this->hasMatches = true;
            $select = $this->buildMatchQuery($scoreBuilder, $select, $query, $conditionType);
        }

        return $select;
    }

    /**
     * @return Select[]
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * @return array
     */
    public function getQueryNames()
    {
        return array_keys($this->getQueries());
    }

    private function addQuery(Select $select)
    {
        $name = self::QUERY_NAME_PREFIX . count($this->queries);
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
     * @param $conditionType
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
