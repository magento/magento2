<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * Term filter builder.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 */
class Term implements FilterInterface
{
    const CONDITION_OPERATOR_EQUALS = '=';
    const CONDITION_OPERATOR_NOT_EQUALS = '!=';
    const CONDITION_OPERATOR_IN = 'IN';
    const CONDITION_OPERATOR_NOT_IN = 'NOT IN';

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
     * @inheritdoc
     */
    public function buildFilter(
        RequestFilterInterface $filter,
        $isNegation
    ) {
        /** @var \Magento\Framework\Search\Request\Filter\Term $filter */

        return $this->conditionManager->generateCondition(
            $filter->getField(),
            $this->getConditionOperator($filter->getValue(), $isNegation),
            $filter->getValue()
        );
    }

    /**
     * Get condition operator.
     *
     * @param string|array $value
     * @param bool $isNegation
     * @return string
     */
    private function getConditionOperator($value, $isNegation)
    {
        if (is_array($value)) {
            $operator = $isNegation ? self::CONDITION_OPERATOR_NOT_IN : self::CONDITION_OPERATOR_IN;
        } else {
            $operator = $isNegation ? self::CONDITION_OPERATOR_NOT_EQUALS : self::CONDITION_OPERATOR_EQUALS;
        }
        return $operator;
    }
}
