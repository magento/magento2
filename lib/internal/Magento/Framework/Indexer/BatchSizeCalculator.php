<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use \Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Batch size calculator can be used to estimate optimal batch size based on the given MySQL MEMORY table.
 *
 * Even if the target table uses a different engine (e.g. InnoDB) this calculator still can be used.
 * Because records in MEMORY table require more space to be stored.
 *
 * If the table was created by the user explicitly and uses MEMORY engine its capacity can be calculated using:
 *
 * select MAX_DATA_LENGTH/AVG_ROW_LENGTH as `max_rows`
 * from information_schema.tables where table_name = '...' and table_schema = '...';
 *
 * Unfortunately this approach does not work on temporary tables.
 */
class BatchSizeCalculator implements BatchSizeCalculatorInterface
{
    /**
     * @inheritdoc
     */
    public function estimateBatchSize(AdapterInterface $adapter, $memoryTableMinRows)
    {
        /**
         * According to MySQL documentation:
         * The maximum size for in-memory temporary tables is determined from whichever of the values of
         * tmp_table_size and max_heap_table_size is smaller.
         */
        $maxHeapTableSize = $adapter->fetchOne('SELECT @@max_heap_table_size;');
        $tmpTableSize = $adapter->fetchOne('SELECT @@tmp_table_size;');
        $maxMemoryTableSize = min($maxHeapTableSize, $tmpTableSize);

        /**
         * According to MySQL documentation minimum value of the max_heap_table_size is 16384.
         * (This system variable sets the maximum size to which user-created MEMORY tables are permitted to grow)
         */
        return (int)($maxMemoryTableSize * ($memoryTableMinRows / 16384));
    }
}
