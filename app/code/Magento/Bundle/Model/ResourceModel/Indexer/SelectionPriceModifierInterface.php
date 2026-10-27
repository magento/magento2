<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Framework\Search\Request\Dimension;

interface SelectionPriceModifierInterface
{
    /**
     * Modify selection price data.
     *
     * @param string $indexTable
     * @param Dimension[] $dimensions
     * @return void
     */
    public function modify(string $indexTable, array $dimensions): void;
}
