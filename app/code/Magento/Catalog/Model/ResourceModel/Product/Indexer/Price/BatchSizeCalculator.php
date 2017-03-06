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
    private $memoryTablesMinRows;

    /**
     * @var \Magento\Framework\Indexer\BatchSizeCalculatorInterface[]
     */
    private $calculators;

    /**
     * BatchSizeCalculator constructor.
     * @param array $memoryTablesMinRows
     * @param array $calculators
     */
    public function __construct(array $memoryTablesMinRows, array $calculators)
    {
        $this->memoryTablesMinRows = $memoryTablesMinRows;
        $this->calculators = $calculators;
    }

    /**
     * Composite object for batch size calculators
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param $indexerTypeId
     * @return int
     */
    public function estimateBatchSize(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $indexerTypeId)
    {
        $memoryTableMinRows = isset($this->memoryTablesMinRows[$indexerTypeId])
            ? $this->memoryTablesMinRows[$indexerTypeId]
            : $this->memoryTablesMinRows['default'];

        /** @var \Magento\Framework\Indexer\BatchSizeCalculatorInterface $calculator */
        $calculator = isset($this->calculators[$indexerTypeId])
            ? $this->calculators[$indexerTypeId]
            : $this->calculators['default'];

        return $calculator->estimateBatchSize($connection, $memoryTableMinRows);
    }
}
