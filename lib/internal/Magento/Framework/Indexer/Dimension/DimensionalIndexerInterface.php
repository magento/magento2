<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Dimension;

/**
 * @api
 * Run indexer by specific dimension
 */
interface DimensionalIndexerInterface
{
    /**
     * Execute indexer by specified dimension.
     * Accept array of dimensions DTO that represent indexer dimension
     *
     * @param \Magento\Framework\Indexer\Dimension[] $dimension
     * @param \Traversable|null $entityIds
     * @return void
     */
    public function executeByDimension(array $dimension, \Traversable $entityIds = null);
}
