<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Apply multiple query modifiers to select
 */
class CompositeQueryModifier implements QueryModifierInterface
{
    /**
     * @var QueryModifierInterface[]
     */
    private $queryModifiers;

    /**
     * Constructor
     *
     * @param QueryModifierInterface[] $queryModifiers
     */
    public function __construct(
        array $queryModifiers = []
    ) {
        $this->queryModifiers = $queryModifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Select $select)
    {
        foreach ($this->queryModifiers as $modifier) {
            $modifier->modify($select);
        }
    }
}
