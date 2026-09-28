<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Indexer;

/**
 * Calculate memory size for entity according different dimensions.
 * @api
 * @since 101.0.0
 */
interface IndexTableRowSizeEstimatorInterface
{
    /**
     * Calculate memory size for entity row.
     *
     * @return float
     * @since 101.0.0
     */
    public function estimateRowSize();
}
