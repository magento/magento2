<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

class CompositeSelectionPriceModifier implements SelectionPriceModifierInterface
{
    /**
     * @param SelectionPriceModifierInterface[] $modifiers
     */
    public function __construct(
        private readonly array $modifiers = []
    ) {
        // Validate that all modifiers implement the correct interface
        array_map(fn (SelectionPriceModifierInterface $modifier) => $modifier, $this->modifiers);
    }

    /**
     * @inheritDoc
     */
    public function modify(string $indexTable, array $dimensions): void
    {
        foreach ($this->modifiers as $modifier) {
            $modifier->modify($indexTable, $dimensions);
        }
    }
}
