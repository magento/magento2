<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
<<<<<<< HEAD
=======
 * @api
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 * Run indexer by dimensions
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
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds);
}
