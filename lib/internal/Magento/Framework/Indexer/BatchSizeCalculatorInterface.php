<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use \Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Batch size calculator can be used to estimate optimal batch size based on the given MEMORY table.
 *
 * Even if the target table uses a different engine (e.g. InnoDB) this calculator still can be used.
 * Because records in MEMORY table require more space to be stored.
 */
interface BatchSizeCalculatorInterface
{
    /**
     * Estimate batch size for the given MEMORY table.
     *
     * @param AdapterInterface $adapter database adapter.
     * @param int $memoryTableMinRows number of records that can be inserted into MEMORY table when its maximum size
     * is limited by the lowest possible value (e.g. max_heap_table_size value set to 16384 in MySQL).
     * @return int number of records in batch
     */
    public function estimateBatchSize(AdapterInterface $adapter, $memoryTableMinRows);
}
