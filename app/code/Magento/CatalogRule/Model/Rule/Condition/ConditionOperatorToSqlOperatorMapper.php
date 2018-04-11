<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\Framework\Exception\InputException;

/**
 * Class ConditionOperatorToSqlOperatorMapper
 * Maps catalog price rule operators to their corresponding operators in SQL
 *
 * @package Magento\CatalogRule\Model\Rule\Condition
 */
class ConditionOperatorToSqlOperatorMapper
{
    /**
     * @var array
     */
    private $operatorsMap = [
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

    /**
     * Maps catalog price rule operators to their corresponding operators in SQL
     *
     * @param $ruleOperator
     * @return mixed
     * @throws InputException
     */
    public function mapConditionOperatorToSQL($ruleOperator)
    {
        if (!array_key_exists($ruleOperator, $this->operatorsMap)) {
            throw new InputException(
                __(sprintf('Undefined rule operator "%s" passed in.', $ruleOperator))
            );
        }

        return $this->operatorsMap[$ruleOperator];
    }
}
