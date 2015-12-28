<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\FilterInterface;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard;

class Builder implements BuilderInterface
{
    /**#@+
     * Text flags for Elasticsearch bulk actions
     */
    const QUERY_OPERATOR_AND = 'AND';
    const QUERY_OPERATOR_OR = 'OR';
    /**#@-*/

    /**
     * @var FilterInterface[]
     */
    protected $filters;

    /**
     * @param Range $range
     * @param Term $term
     * @param Wildcard $wildcard
     */
    public function __construct(
        Range $range,
        Term $term,
        Wildcard $wildcard
    ) {
        $this->filters = [
            RequestFilterInterface::TYPE_RANGE => $range,
            RequestFilterInterface::TYPE_TERM => $term,
            RequestFilterInterface::TYPE_WILDCARD => $wildcard,
        ];
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
     * @return array
     */
    protected function processFilter(RequestFilterInterface $filter, $isNegation)
    {
        if (RequestFilterInterface::TYPE_BOOL == $filter->getType()) {
            $query = $this->processBoolFilter($filter, $isNegation);
        } else {
            if (!array_key_exists($filter->getType(), $this->filters)) {
                throw new \InvalidArgumentException('Unknown filter type ' . $filter->getType());
            }
            $query = $this->filters[$filter->getType()]->buildFilter($filter);
            if ($isNegation) {
                $query = ['not' => $query];
            }
        }

        return $query;
    }

    /**
     * @param RequestFilterInterface|\Magento\Framework\Search\Request\Filter\BoolExpression $filter
     * @param bool $isNegation
     * @return array
     */
    protected function processBoolFilter(RequestFilterInterface $filter, $isNegation)
    {
        $must = $this->buildFilters($filter->getMust(), self::QUERY_OPERATOR_AND, $isNegation);
        $should = $this->buildFilters($filter->getShould(), self::QUERY_OPERATOR_OR, $isNegation);
        $mustNot = $this->buildFilters($filter->getMustNot(), self::QUERY_OPERATOR_AND, !$isNegation);

        $queries = [
            'bool' => [
                'must' => $must,
                'should' => $should,
                'must_not' => $mustNot,
            ]
        ];

        return $queries;
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
            $queries[] = $this->processFilter($filter, $isNegation);
        }
        if ($unionOperator === self::QUERY_OPERATOR_OR) {
            return [
                'or' => $queries,
            ];
        }
        return $queries;
    }

    /**
     * @param string $conditionType
     * @return bool
     */
    protected function isNegation($conditionType)
    {
        return BoolExpression::QUERY_CONDITION_NOT === $conditionType;
    }
}
