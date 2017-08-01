<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term
 *
 * @since 2.0.0
 */
class Term implements FilterInterface
{
    const CONDITION_OPERATOR_EQUALS = '=';
    const CONDITION_OPERATOR_NOT_EQUALS = '!=';
    const CONDITION_OPERATOR_IN = 'IN';
    const CONDITION_OPERATOR_NOT_IN = 'NOT IN';

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
        /** @var \Magento\Framework\Search\Request\Filter\Term $filter */

        return $this->conditionManager->generateCondition(
            $filter->getField(),
            $this->getConditionOperator($filter->getValue(), $isNegation),
            $filter->getValue()
        );
    }

    /**
     * @param string|array $value
     * @param bool $isNegation
     * @return string
     * @since 2.0.0
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
