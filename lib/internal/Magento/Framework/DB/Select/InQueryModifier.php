<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Add in condition to select
 */
class InQueryModifier implements QueryModifierInterface
{
    /**
     * @var array
     */
    private $values;

    public function __constructor($values)
    {
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
