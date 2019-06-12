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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
