<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\FilterInterface;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Wildcard;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\Query\Bool;

class Builder implements BuilderInterface
{
    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * @var PreprocessorInterface
     */
    private $preprocessor;

    /**
     * @param Range $range
     * @param Term $term
     * @param Wildcard $wildcard
     * @param ConditionManager $conditionManager
     * @param PreprocessorInterface $preprocessor
     */
    public function __construct(
        Range $range,
        Term $term,
        Wildcard $wildcard,
        ConditionManager $conditionManager,
        PreprocessorInterface $preprocessor
    ) {
        $this->filters = [
            RequestFilterInterface::TYPE_RANGE => $range,
            RequestFilterInterface::TYPE_TERM => $term,
            RequestFilterInterface::TYPE_WILDCARD => $wildcard,
        ];
        $this->conditionManager = $conditionManager;
        $this->preprocessor = $preprocessor;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestFilterInterface $filter, $conditionType, QueryContainer $queryContainer)
    {
        return $this->processFilter($filter, $this->isNegation($conditionType), $queryContainer);
    }

    /**
     * @param RequestFilterInterface $filter
     * @param bool $isNegation
     * @param QueryContainer $queryContainer
     * @return string
     */
    private function processFilter(RequestFilterInterface $filter, $isNegation, QueryContainer $queryContainer)
    {
        if ($filter->getType() == RequestFilterInterface::TYPE_BOOL) {
            $query = $this->processBoolFilter($filter, $isNegation, $queryContainer);
            $query = $this->conditionManager->wrapBrackets($query);
        } else {
            if (!isset($this->filters[$filter->getType()])) {
                throw new \InvalidArgumentException('Unknown filter type ' . $filter->getType());
            }
            $query = $this->filters[$filter->getType()]->buildFilter($filter, $isNegation);
            $query = $this->preprocessor->process($filter, $isNegation, $query, $queryContainer);
        }

        return $query;
    }

    /**
     * @param RequestFilterInterface|\Magento\Framework\Search\Request\Filter\Bool $filter
     * @param bool $isNegation
     * @param QueryContainer $queryContainer
     * @return string
     */
    private function processBoolFilter(RequestFilterInterface $filter, $isNegation, QueryContainer $queryContainer)
    {
        $must = $this->buildFilters($filter->getMust(), Select::SQL_AND, $isNegation, $queryContainer);
        $should = $this->buildFilters($filter->getShould(), Select::SQL_OR, $isNegation, $queryContainer);
        $mustNot = $this->buildFilters(
            $filter->getMustNot(),
            Select::SQL_AND,
            !$isNegation,
            $queryContainer
        );

        $queries = [
            $must,
            $this->conditionManager->wrapBrackets($should),
            $this->conditionManager->wrapBrackets($mustNot),
        ];

        return $this->conditionManager->combineQueries($queries, Select::SQL_AND);
    }

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface[] $filters
     * @param string $unionOperator
     * @param bool $isNegation
     * @param QueryContainer $queryContainer
     * @return string
     */
    private function buildFilters(array $filters, $unionOperator, $isNegation, QueryContainer $queryContainer)
    {
        $queries = [];
        foreach ($filters as $filter) {
            $queries[] = $this->processFilter($filter, $isNegation, $queryContainer);
        }
        return $this->conditionManager->combineQueries($queries, $unionOperator);
    }

    /**
     * @param string $conditionType
     * @return bool
     */
    private function isNegation($conditionType)
    {
        return Bool::QUERY_CONDITION_NOT === $conditionType;
    }
}
