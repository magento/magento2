<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\InputException;

/**
 * Class ConditionOperatorToSqlOperatorMapper
 * Maps catalog price rule aggregator names to their corresponding operators in SQL
 *
 * @package Magento\CatalogRule\Model\Rule\Condition
 */
class ConditionAggregatorToSqlOperatorMapper
{
    /**
     * @var array
     */
    private $operatorsMap = [
        'all'    => Select::SQL_AND,
        'any'    => Select::SQL_OR,
    ];

    /**
     * Maps catalog price rule operators to their corresponding operators in SQL
     *
     * @param $ruleAggregator
     * @return string
     * @throws InputException
     */
    public function mapConditionAggregatorToSQL(string $ruleAggregator): string
    {
        if (!array_key_exists(strtolower($ruleAggregator), $this->operatorsMap)) {
            throw new InputException(
                __(sprintf('Undefined rule aggregator "%s" passed in.', $ruleAggregator))
            );
        }

        return $this->operatorsMap[$ruleAggregator];
    }
}
