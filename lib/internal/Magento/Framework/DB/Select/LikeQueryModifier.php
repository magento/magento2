<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Add LIKE condition to select
 * @since 2.2.0
 */
class LikeQueryModifier implements QueryModifierInterface
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $values;

    /**
     * Constructor
     *
     * @param array $values array of field and pattern pairs of Like Clause,
     *                      for example: [<field1> => <pattern1>, <field2> => <pattern2>, ...]
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
        foreach ($this->values as $field => $pattern) {
            $select->where($field . ' LIKE (?)', $pattern);
        }
    }
}
