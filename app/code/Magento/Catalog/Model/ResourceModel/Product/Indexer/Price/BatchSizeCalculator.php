<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Ensure that size of index MEMORY table is enough for configured rows count in batch.
 */
class BatchSizeCalculator
{
    /**
     * @var array
     */
    private $batchRowsCount;

    /**
     * @var \Magento\Framework\Indexer\BatchSizeManagementInterface[]
     */
    private $estimators;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductBatchSizeAdjusterInterface[]
     */
    private $batchSizeAdjusters;

    /**
     * BatchSizeCalculator constructor.
     * @param array $batchRowsCount
     * @param array $estimators
     * @param array $batchSizeAdjusters
     */
    public function __construct(array $batchRowsCount, array $estimators, array $batchSizeAdjusters)
    {
        $this->batchRowsCount = $batchRowsCount;
        $this->estimators = $estimators;
        $this->batchSizeAdjusters = $batchSizeAdjusters;
    }

    /**
     * Retrieve batch size for the given indexer.
     *
     * Ensure that the database will be able to handle provided batch size correctly.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $indexerTypeId
     * @return int
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

        $batchRowsCount = isset($this->batchSizeAdjusters[$indexerTypeId])
            ? $this->batchSizeAdjusters[$indexerTypeId]->adjust($batchRowsCount)
            : $batchRowsCount;

        $calculator->ensureBatchSize($connection, $batchRowsCount);

        return $batchRowsCount;
    }
}
