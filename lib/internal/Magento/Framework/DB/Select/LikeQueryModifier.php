<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Add LIKE condition to select
 */
class LikeQueryModifier implements QueryModifierInterface
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
            $select->where($field . ' LIKE (?)', $values);
        }
    }
}
