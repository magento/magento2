<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Add IN condition to select
 * @since 2.2.0
 */
class InQueryModifier implements QueryModifierInterface
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $values;

    /**
     * Constructor
     *
     * @param array $values
     * @since 2.2.0
     */
    public function __construct(
        $values = []
    ) {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function modify(Select $select)
    {
        foreach ($this->values as $field => $values) {
            $select->where($field . ' IN (?)', $values);
        }
    }
}
