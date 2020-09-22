<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
