<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\Framework\Exception\InputException;

/**
 * Class ConditionOperatorToSqlOperatorMapper
 * Makes SQL operator reversed
 * For example '=' will become '!='
 *
 * @package Magento\CatalogRule\Model\Rule\Condition
 */
class ReverseSqlOperator
{
    /**
     * @var array
     */
    private $operatorsMap = [
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

    /**
     * Makes SQL operator reversed
     *
     * @param $operator
     * @return mixed
     * @throws InputException
     */
    public function reverseOperator(string $operator): string
    {
        if (!array_key_exists($operator, $this->operatorsMap)) {
            throw new InputException(
                __(sprintf('Undefined SQL operator "%s" passed in.', $operator))
            );
        }

        return $this->operatorsMap[$operator];
    }
}
