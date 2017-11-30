<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument\Find;

/**
 * Class Represents logical connective whenever a condition will nest/branch.
 *
 * A clause can be branched by "and" or "or" Operator and has a list of conditions as sub clauses.
 */
class Connective
{
    /**
     * @var string
     */
    private $operator;

    /**
     * @var array
     */
    private $conditions;

    /**
     * @param Operator $operator
     * @param array $conditions
     */
    public function __construct(
        Operator $operator,
        array $conditions
    ) {
        $this->operator = $operator;
        $this->conditions = $conditions;
    }

    /**
     * @return Operator
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return Connective[]|Clause[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }
}
