<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Add IN condition to select
 */
class InQueryModifier implements QueryModifierInterface
{
    /**
     * @var array
     */
    private $values;

    /**
     * Constructor
     *
     * @param array $values
     */
    public function __construct(
        $values = []
    ) {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Select $select)
    {
        foreach ($this->values as $field => $values) {
            $select->where($field . ' IN (?)', $values);
        }
    }
}
