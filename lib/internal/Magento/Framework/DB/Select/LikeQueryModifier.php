<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param array $values array of field and pattern pairs of Like Clause,
     *                      for example: [<field1> => <pattern1>, <field2> => <pattern2>, ...]
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
        foreach ($this->values as $field => $pattern) {
            $select->where($field . ' LIKE (?)', $pattern);
        }
    }
}
