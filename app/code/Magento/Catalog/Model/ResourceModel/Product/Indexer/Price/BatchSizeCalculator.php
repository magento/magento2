<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

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
     * BatchSizeCalculator constructor.
     * @param array $batchRowsCount
     * @param array $estimators
     */
    public function __construct(array $batchRowsCount, array $estimators)
    {
        $this->batchRowsCount = $batchRowsCount;
        $this->estimators = $estimators;
    }

    /**
     * Composite object for batch size calculators
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

        $calculator->ensureBatchSize($connection, $batchRowsCount);

        return $batchRowsCount;
    }
}
