<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range
 *
 * @since 2.0.0
 */
class Range implements FilterInterface
{
    const CONDITION_PART_GREATER_THAN = '>=';
    const CONDITION_PART_LOWER_THAN = '<=';
    const CONDITION_NEGATION_PART_GREATER_THAN = '>';
    const CONDITION_NEGATION_PART_LOWER_THAN = '<';

    /**
     * @var ConditionManager
     * @since 2.0.0
     */
    private $conditionManager;

    /**
     * @param ConditionManager $conditionManager
     * @since 2.0.0
     */
    public function __construct(
        ConditionManager $conditionManager
    ) {
        $this->conditionManager = $conditionManager;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function buildFilter(
        RequestFilterInterface $filter,
        $isNegation
    ) {
        /** @var RangeFilterRequest $filter */
        $queries = [
            $this->getLeftConditionPart($filter, $isNegation),
            $this->getRightConditionPart($filter, $isNegation),
        ];
        $unionOperator = $this->getConditionUnionOperator($isNegation);

        return $this->conditionManager->combineQueries($queries, $unionOperator);
    }

    /**
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @param bool $isNegation
     * @return string
     * @since 2.0.0
     */
    private function getLeftConditionPart(RequestFilterInterface $filter, $isNegation)
    {
        return $this->getPart(
            $filter->getField(),
            ($isNegation ? self::CONDITION_NEGATION_PART_LOWER_THAN : self::CONDITION_PART_GREATER_THAN),
            $filter->getFrom()
        );
    }

    /**
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @param bool $isNegation
     * @return string
     * @since 2.0.0
     */
    private function getRightConditionPart(RequestFilterInterface $filter, $isNegation)
    {
        return $this->getPart(
            $filter->getField(),
            ($isNegation ? self::CONDITION_NEGATION_PART_GREATER_THAN : self::CONDITION_PART_LOWER_THAN),
            $filter->getTo()
        );
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    private function getPart($field, $operator, $value)
    {
        return $value === null
            ? ''
            : $this->conditionManager->generateCondition($field, $operator, $value);
    }

    /**
     * @param bool $isNegation
     * @return string
     * @since 2.0.0
     */
    private function getConditionUnionOperator($isNegation)
    {
        return $isNegation ? \Magento\Framework\DB\Select::SQL_OR : \Magento\Framework\DB\Select::SQL_AND;
    }
}
