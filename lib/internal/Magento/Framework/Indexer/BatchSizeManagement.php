<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

class BatchSizeManagement implements \Magento\Framework\Indexer\BatchSizeManagementInterface
{
    /**
     * @var \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface
     */
    private $rowSizeEstimator;

    /**
     * CompositeProductBatchSizeCalculator constructor.
     * @param \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface $rowSizeEstimator
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface $rowSizeEstimator
    ) {
        $this->rowSizeEstimator = $rowSizeEstimator;
    }

    /**
     * @inheritdoc
     */
    public function ensureBatchSize(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $batchSize)
    {
        // Calculate memory table size for product
        $memoryForLargeComposite = $this->rowSizeEstimator->estimateRowSize();

        $maxHeapTableSize = $connection->fetchOne('SELECT @@max_heap_table_size;');
        $tmpTableSize = $connection->fetchOne('SELECT @@tmp_table_size;');
        $maxMemoryTableSize = min($maxHeapTableSize, $tmpTableSize);

        $size = (int) ($memoryForLargeComposite * $batchSize);

        if ($maxMemoryTableSize < $size) {
            $connection->query('SET SESSION tmp_table_size = ' . $size . ';');
            $connection->query('SET SESSION max_heap_table_size = ' . $size . ';');
        }
    }
}
