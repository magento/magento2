<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Indexer;

/**
 * Class set MEMORY table size for indexer processes according batch size and index row size.
 * @since 2.2.0
 */
class BatchSizeManagement implements \Magento\Framework\Indexer\BatchSizeManagementInterface
{
    /**
     * @var \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface
     * @since 2.2.0
     */
    private $rowSizeEstimator;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * CompositeProductBatchSizeCalculator constructor.
     * @param \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface $rowSizeEstimator
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface $rowSizeEstimator,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->rowSizeEstimator = $rowSizeEstimator;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function ensureBatchSize(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $batchSize)
    {
        $rowMemory = $this->rowSizeEstimator->estimateRowSize();

        $maxHeapTableSize = $connection->fetchOne('SELECT @@max_heap_table_size;');
        $tmpTableSize = $connection->fetchOne('SELECT @@tmp_table_size;');
        $bufferPoolSize = $connection->fetchOne('SELECT @@innodb_buffer_pool_size;');
        $maxMemoryTableSize = min($maxHeapTableSize, $tmpTableSize);

        $size = (int) ($rowMemory * $batchSize);

        // Log warning if allocated memory for temp table greater than 20% of innodb_buffer_pool_size
        if ($size > $bufferPoolSize * .2) {
            $message = 'Memory size allocated for the temporary table is more than 20% of innodb_buffer_pool_size. ' .
                'Please update innodb_buffer_pool_size or decrease batch size value '.
                '(which decreases memory usages for the temporary table). ' .
                'Current batch size: %1; Allocated memory size: %2 bytes; InnoDB buffer pool size: %3 bytes.';
            $this->logger->warning(new \Magento\Framework\Phrase($message, [$batchSize, $size, $bufferPoolSize]));
        }

        if ($maxMemoryTableSize < $size) {
            $connection->query('SET SESSION tmp_table_size = ' . $size . ';');
            $connection->query('SET SESSION max_heap_table_size = ' . $size . ';');
        }
    }
}
