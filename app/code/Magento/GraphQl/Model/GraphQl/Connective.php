<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\Model\GraphQl;

/**
 * Class Represents logical connective
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
