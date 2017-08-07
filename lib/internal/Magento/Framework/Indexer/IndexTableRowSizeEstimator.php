<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

/**
 * Generic implementation for row size estimation.
 * @since 2.2.0
 */
class IndexTableRowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * @var int
     * @since 2.2.0
     */
    private $rowMemorySize;

    /**
     * @param int $rowMemorySize
     * @since 2.2.0
     */
    public function __construct($rowMemorySize)
    {
        $this->rowMemorySize = $rowMemorySize;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function estimateRowSize()
    {
        return $this->rowMemorySize;
    }
}
