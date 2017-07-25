<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

/**
 * Calculate memory size for entity according different dimensions.
 * @api
 */
interface IndexTableRowSizeEstimatorInterface
{
    /**
     * Calculate memory size for entity row.
     *
     * @return float
     */
    public function estimateRowSize();
}
