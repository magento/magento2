<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\FilterInterface;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Wildcard;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    public function build(RequestFilterInterface $filter, $conditionType)
    {
        return $this->processFilter($filter, $this->isNegation($conditionType));
    }

    /**
     * @param RequestFilterInterface $filter
     * @param bool $isNegation
     * @return string
     */
    private function processFilter(RequestFilterInterface $filter, $isNegation)
    {
        if ($filter->getType() === RequestFilterInterface::TYPE_BOOL) {
            $query = $this->processBoolFilter($filter, $isNegation);
            $query = $this->conditionManager->wrapBrackets($query);
        } else {
            if (!isset($this->filters[$filter->getType()])) {
                throw new \InvalidArgumentException('Unknown filter type ' . $filter->getType());
            }
            $query = $this->filters[$filter->getType()]->buildFilter($filter, $isNegation);
            $query = $this->preprocessor->process($filter, $isNegation, $query);
        }

        return $query;
    }

    /**
     * @param RequestFilterInterface|\Magento\Framework\Search\Request\Filter\Bool $filter
     * @param bool $isNegation
     * @return string
     */
    private function processBoolFilter(RequestFilterInterface $filter, $isNegation)
    {
        $must = $this->buildFilters($filter->getMust(), Select::SQL_AND, $isNegation);
        $should = $this->buildFilters($filter->getShould(), Select::SQL_OR, $isNegation);
        $mustNot = $this->buildFilters(
            $filter->getMustNot(),
            Select::SQL_AND,
            !$isNegation
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
     * @return string
     */
    private function buildFilters(array $filters, $unionOperator, $isNegation)
    {
        $queries = [];
        foreach ($filters as $filter) {
            $filterQuery = $this->processFilter($filter, $isNegation);
            $queries[] = $this->conditionManager->wrapBrackets($filterQuery);
        }
        return $this->conditionManager->combineQueries($queries, $unionOperator);
    }

    /**
     * @param string $conditionType
     * @return bool
     */
    private function isNegation($conditionType)
    {
        return BoolExpression::QUERY_CONDITION_NOT === $conditionType;
    }
}
