<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

class Range implements FilterInterface
{
    const CONDITION_PART_GREATER_THAN = '>=';
    const CONDITION_PART_LOWER_THAN = '<=';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @param ConditionManager $conditionManager
     */
    public function __construct(
        ConditionManager $conditionManager
    ) {
        $this->conditionManager = $conditionManager;
    }

    /**
     * {@inheritdoc}
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
     */
    private function getLeftConditionPart(RequestFilterInterface $filter, $isNegation)
    {
        return $this->getPart(
            $filter->getField(),
            ($isNegation ? self::CONDITION_PART_LOWER_THAN : self::CONDITION_PART_GREATER_THAN),
            $filter->getFrom()
        );
    }

    /**
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @param bool $isNegation
     * @return string
     */
    private function getRightConditionPart(RequestFilterInterface $filter, $isNegation)
    {
        return $this->getPart(
            $filter->getField(),
            ($isNegation ? self::CONDITION_PART_GREATER_THAN : self::CONDITION_PART_LOWER_THAN),
            $filter->getTo()
        );
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return string
     */
    private function getPart($field, $operator, $value)
    {
        return is_null($value)
            ? ''
            : $this->conditionManager->generateCondition($field, $operator, $value);
    }

    /**
     * @param bool $isNegation
     * @return string
     */
    private function getConditionUnionOperator($isNegation)
    {
        return $isNegation ? \Zend_Db_Select::SQL_OR : \Zend_Db_Select::SQL_AND;
    }
}
