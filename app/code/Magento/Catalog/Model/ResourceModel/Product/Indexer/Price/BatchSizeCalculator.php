<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Ensure that size of index MEMORY table is enough for configured rows count in batch.
 * @since 2.2.0
 */
class BatchSizeCalculator
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $batchRowsCount;

    /**
     * @var \Magento\Framework\Indexer\BatchSizeManagementInterface[]
     * @since 2.2.0
     */
    private $estimators;

    /**
     * BatchSizeCalculator constructor.
     * @param array $batchRowsCount
     * @param array $estimators
     * @since 2.2.0
     */
    public function __construct(array $batchRowsCount, array $estimators)
    {
        $this->batchRowsCount = $batchRowsCount;
        $this->estimators = $estimators;
    }

    /**
     * Retrieve batch size for the given indexer.
     *
     * Ensure that the database will be able to handle provided batch size correctly.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $indexerTypeId
     * @return int
     * @since 2.2.0
     */
    public function estimateBatchSize(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $indexerTypeId)
    {
        $batchRowsCount = isset($this->batchRowsCount[$indexerTypeId])
            ? $this->batchRowsCount[$indexerTypeId]
            : $this->batchRowsCount['default'];

        /** @var \Magento\Framework\Indexer\BatchSizeManagementInterface $calculator */
        $calculator = isset($this->estimators[$indexerTypeId])
            ? $this->estimators[$indexerTypeId]
            : $this->estimators['default'];

        $calculator->ensureBatchSize($connection, $batchRowsCount);

        return $batchRowsCount;
    }
}
