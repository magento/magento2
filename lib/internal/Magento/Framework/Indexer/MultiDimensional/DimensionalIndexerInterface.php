<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\Indexer\MultiDimensional;

/**
 * Run indexer by specific dimension
 */
interface DimensionalIndexerInterface
{
    /**
     * Execute indexer by specified dimension
     *
     * @param int[] $ids
     * @param Dimension[] $dimensions
     * @return mixed
     */
    public function executeWithinDimensions(array $ids = [], array $dimensions = []);
}