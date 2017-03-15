<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

/**
 * Generic implementation for row size estimation.
 */
class IndexTableRowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * @var int
     */
    private $rowMemorySize;

    /**
     * @param int $rowMemorySize
     */
    public function __construct($rowMemorySize)
    {
        $this->rowMemorySize = $rowMemorySize;
    }

    /**
     * @inheritdoc
     */
    public function estimateRowSize()
    {
        return $this->rowMemorySize;
    }
}
