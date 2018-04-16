<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\Framework\Exception\InputException;
use Magento\Rule\Model\Condition\ConditionInterface;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CombinedCondition;
use Magento\CatalogRule\Model\Rule\Condition\Product as SimpleCondition;
use Magento\Framework\Api\CombinedFilterGroup as FilterGroup;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria;

/**
 * Maps catalog price rule conditions to search criteria
 */
class ConditionsToSearchCriteriaMapper
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var \Magento\Framework\Api\CombinedFilterGroupFactory
     */
    private $combinedFilterGroupFactory;

    /**
     * @var \Magento\Framework\Api\FilterFactory
     */
    private $filterFactory;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param \Magento\Framework\Api\CombinedFilterGroupFactory $combinedFilterGroupFactory
     * @param \Magento\Framework\Api\FilterFactory $filterFactory
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        \Magento\Framework\Api\CombinedFilterGroupFactory $combinedFilterGroupFactory,
        \Magento\Framework\Api\FilterFactory $filterFactory
    ) {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->combinedFilterGroupFactory = $combinedFilterGroupFactory;
        $this->filterFactory = $filterFactory;
    }

    /**
     * Maps catalog price rule conditions to search criteria
     *
     * @param CombinedCondition $conditions
     * @return SearchCriteria
     * @throws InputException
     */
    public function mapConditionsToSearchCriteria(CombinedCondition $conditions): SearchCriteria
    {
        $filterGroup = $this->mapCombinedConditionToFilterGroup($conditions);

        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        if ($filterGroup !== null) {
            $searchCriteriaBuilder->setFilterGroups([$filterGroup]);
        }

        return $searchCriteriaBuilder->create();
    }

    /**
     * @param ConditionInterface $condition
     * @return null|\Magento\Framework\Api\CombinedFilterGroup|\Magento\Framework\Api\Filter
     * @throws InputException
     */
    private function mapConditionToFilterGroup(ConditionInterface $condition)
    {
        if ($condition->getType() === CombinedCondition::class) {
            return $this->mapCombinedConditionToFilterGroup($condition);
        } elseif ($condition->getType() === SimpleCondition::class) {
            return $this->mapSimpleConditionToFilterGroup($condition);
        }

        throw new InputException(
            __('Undefined condition type "%1" passed in.', $condition->getType())
        );
    }

    /**
     * @param Combine $combinedCondition
     * @return null|\Magento\Framework\Api\CombinedFilterGroup
     * @throws InputException
     */
    private function mapCombinedConditionToFilterGroup(CombinedCondition $combinedCondition)
    {
        $filters = [];

        foreach ($combinedCondition->getConditions() as $condition) {
            $filter = $this->mapConditionToFilterGroup($condition);

            if ($filter === null) {
                continue;
            }

            // This required to solve cases when condition is configured like:
            // "If ALL/ANY of these conditions are FALSE" - we need to reverse SQL operator for this "FALSE"
            if ((bool)$combinedCondition->getValue() === false) {
                $this->reverseSqlOperatorInFilter($filter);
            }

            $filters[] = $filter;
        }

        if (count($filters) === 0) {
            return null;
        }

        return $this->createCombinedFilterGroup($filters, $combinedCondition->getAggregator());
    }

    /**
     * @param ConditionInterface $productCondition
     * @return FilterGroup|Filter
     * @throws InputException
     */
    private function mapSimpleConditionToFilterGroup(ConditionInterface $productCondition)
    {
        if (is_array($productCondition->getValue())) {
            return $this->processSimpleConditionWithArrayValue($productCondition);
        }

        return $this->createFilter(
            $productCondition->getAttribute(),
            (string) $productCondition->getValue(),
            $productCondition->getOperator()
        );
    }

    /**
     * @param ConditionInterface $productCondition
     * @return FilterGroup
     * @throws InputException
     */
    private function processSimpleConditionWithArrayValue(ConditionInterface $productCondition): FilterGroup
    {
        $filters = [];

        foreach ($productCondition->getValue() as $subValue) {
            $filters[] = $this->createFilter(
                $productCondition->getAttribute(),
                (string) $subValue,
                $productCondition->getOperator()
            );
        }

        $combinationMode = $this->getGlueForArrayValues($productCondition->getOperator());

        return $this->createCombinedFilterGroup($filters, $combinationMode);
    }

    /**
     * @param string $operator
     * @return string
     */
    private function getGlueForArrayValues(string $operator): string
    {
        if (in_array($operator, ['!=', '!{}', '!()'], true)) {
            return 'all';
        }

        return 'any';
    }

    /**
     * Reverse sql conditions to their corresponding negative analog
     *
     * @param Filter $filter
     * @return void
     * @throws InputException
     */
    private function reverseSqlOperatorInFilter(Filter $filter)
    {
        $operatorsMap = [
            'eq'    => 'neq',
            'neq'   => 'eq',
            'gteq'  => 'lt',
            'lteq'  => 'gt',
            'gt'    => 'lteq',
            'lt'    => 'gteq',
            'like'  => 'nlike',
            'nlike' => 'like',
            'in'    => 'nin',
            'nin'   => 'in',
        ];

        if (!array_key_exists($filter->getConditionType(), $operatorsMap)) {
            throw new InputException(
                __(
                    'Undefined SQL operator "%1" passed in. Valid operators are: %2',
                    $filter->getConditionType(),
                    implode(',', array_keys($operatorsMap))
                )
            );
        }

        $filter->setConditionType(
            $operatorsMap[$filter->getConditionType()]
        );
    }

    /**
     * @param array $filters
     * @param string $combinationMode
     * @return FilterGroup
     * @throws InputException
     */
    private function createCombinedFilterGroup(array $filters, string $combinationMode): FilterGroup
    {
        return $this->combinedFilterGroupFactory->create([
            'data' => [
                FilterGroup::FILTERS => $filters,
                FilterGroup::COMBINATION_MODE => $this->mapRuleAggregatorToSQLAggregator($combinationMode)
            ]
        ]);
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $conditionType
     * @return Filter
     * @throws InputException
     */
    private function createFilter(string $field, string $value, string $conditionType): Filter
    {
        return $this->filterFactory->create([
            'data' => [
                Filter::KEY_FIELD => $field,
                Filter::KEY_VALUE => $value,
                Filter::KEY_CONDITION_TYPE => $this->mapRuleOperatorToSQLCondition($conditionType)
            ]
        ]);
    }

    /**
     * Maps catalog price rule operators to their corresponding operators in SQL
     *
     * @param string $ruleOperator
     * @return string
     * @throws InputException
     */
    private function mapRuleOperatorToSQLCondition(string $ruleOperator): string
    {
        $operatorsMap = [
            '=='    => 'eq',    // is
            '!='    => 'neq',   // is not
            '>='    => 'gteq',  // equals or greater than
            '<='    => 'lteq',  // equals or less than
            '>'     => 'gt',    // greater than
            '<'     => 'lt',    // less than
            '{}'    => 'like',  // contains
            '!{}'   => 'nlike', // does not contains
            '()'    => 'in',    // is one of
            '!()'   => 'nin',   // is not one of
        ];

        if (!array_key_exists($ruleOperator, $operatorsMap)) {
            throw new InputException(
                __(
                    'Undefined rule operator "%1" passed in. Valid operators are: %2',
                    $ruleOperator,
                    implode(',', array_keys($operatorsMap))
                )
            );
        }

        return $operatorsMap[$ruleOperator];
    }

    /**
     * Map rule combine aggregations to corresponding SQL operator
     *
     * @param string $ruleAggregator
     * @return string
     * @throws InputException
     */
    private function mapRuleAggregatorToSQLAggregator(string $ruleAggregator): string
    {
        $operatorsMap = [
            'all'    => 'AND',
            'any'    => 'OR',
        ];

        if (!array_key_exists(strtolower($ruleAggregator), $operatorsMap)) {
            throw new InputException(
                __(
                    'Undefined rule aggregator "%1" passed in. Valid operators are: %2',
                    $ruleAggregator,
                    implode(',', array_keys($operatorsMap))
                )
            );
        }

        return $operatorsMap[$ruleAggregator];
    }
}
