<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * @api
 * Run indexer by dimensions
 * @since 101.0.6
 */
interface DimensionalIndexerInterface
{
    /**
     * Execute indexer by specified dimension.
     * Accept array of dimensions DTO that represent indexer dimension
     *
     * @param \Magento\Framework\Indexer\Dimension[] $dimensions
     * @param \Traversable $entityIds
     * @return void
     * @since 101.0.6
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds);
}
