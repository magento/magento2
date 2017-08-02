<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Apply multiple query modifiers to select
 * @since 2.2.0
 */
class CompositeQueryModifier implements QueryModifierInterface
{
    /**
     * @var QueryModifierInterface[]
     * @since 2.2.0
     */
    private $queryModifiers;

    /**
     * Constructor
     *
     * @param QueryModifierInterface[] $queryModifiers
     * @since 2.2.0
     */
    public function __construct(
        array $queryModifiers = []
    ) {
        $this->queryModifiers = $queryModifiers;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function modify(Select $select)
    {
        foreach ($this->queryModifiers as $modifier) {
            $modifier->modify($select);
        }
    }
}
