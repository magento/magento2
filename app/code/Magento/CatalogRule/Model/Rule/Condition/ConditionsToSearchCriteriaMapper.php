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
 * Class ConditionsToSearchCriteriaMapper
 * Maps catalog price rule conditions to search criteria
 *
 * @package Magento\CatalogRule\Model\Rule\Condition
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var ConditionAggregatorToSqlOperatorMapper
     */
    private $aggregatorNameToSql;

    /**
     * @var ConditionOperatorToSqlOperatorMapper
     */
    private $operatorTypeToSql;

    /**
     * @var ReverseSqlOperator
     */
    private $reverseSqlOperator;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param \Magento\Framework\Api\CombinedFilterGroupFactory $combinedFilterGroupFactory
     * @param \Magento\Framework\Api\FilterFactory $filterFactory
     * @param ConditionAggregatorToSqlOperatorMapper $aggregatorNameToSql
     * @param ConditionOperatorToSqlOperatorMapper $operatorTypeToSql
     * @param ReverseSqlOperator $reverseSqlOperator
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        \Magento\Framework\Api\CombinedFilterGroupFactory $combinedFilterGroupFactory,
        \Magento\Framework\Api\FilterFactory $filterFactory,
        \Magento\CatalogRule\Model\Rule\Condition\ConditionAggregatorToSqlOperatorMapper $aggregatorNameToSql,
        \Magento\CatalogRule\Model\Rule\Condition\ConditionOperatorToSqlOperatorMapper $operatorTypeToSql,
        \Magento\CatalogRule\Model\Rule\Condition\ReverseSqlOperator $reverseSqlOperator
    ) {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->combinedFilterGroupFactory = $combinedFilterGroupFactory;
        $this->filterFactory = $filterFactory;
        $this->aggregatorNameToSql = $aggregatorNameToSql;
        $this->operatorTypeToSql = $operatorTypeToSql;
        $this->reverseSqlOperator = $reverseSqlOperator;
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
            __(sprintf('Undefined condition type "%s" passed in.', $condition->getType()))
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
            // "If ALL/ANY of these conditions are FALSE"
            // - we need to reverse SQL operator for this "FALSE"
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
     * @return CombinedCondition|SimpleCondition
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
     * @return CombinedCondition
     * @throws InputException
     */
    private function processSimpleConditionWithArrayValue(ConditionInterface $productCondition): CombinedCondition
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
     * @param $operator
     * @return string
     */
    private function getGlueForArrayValues(string $operator): string
    {
        if (in_array($operator, ['!=', '!{}', '!()'])) {
            return 'all';
        }

        return 'any';
    }

    /**
     * @param Filter $filter
     * @return void
     * @throws InputException
     */
    private function reverseSqlOperatorInFilter(Filter $filter)
    {
        $filter->setConditionType(
            $this->reverseSqlOperator->reverseOperator($filter->getConditionType())
        );
    }

    /**
     * @param $filters
     * @param $combinationMode
     * @return CombinedCondition
     * @throws InputException
     */
    private function createCombinedFilterGroup(array $filters, string $combinationMode): CombinedCondition
    {
        return $this->combinedFilterGroupFactory->create([
            'data' => [
                FilterGroup::FILTERS => $filters,
                FilterGroup::COMBINATION_MODE => $this->aggregatorNameToSql->mapConditionAggregatorToSQL(
                    $combinationMode
                )
            ]
        ]);
    }

    /**
     * @param $field
     * @param $value
     * @param $conditionType
     * @return SimpleCondition
     * @throws InputException
     */
    private function createFilter(string $field, string $value, string $conditionType): SimpleCondition
    {
        return $this->filterFactory->create([
            'data' => [
                Filter::KEY_FIELD => $field,
                Filter::KEY_VALUE => $value,
                Filter::KEY_CONDITION_TYPE => $this->operatorTypeToSql->mapConditionOperatorToSQL(
                    $conditionType
                )
            ]
        ]);
    }
}
