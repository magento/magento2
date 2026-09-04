<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Indexer;

use \Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Batch size manager can be used to ensure that MEMORY table has enough memory for data in batch.
 * @api
 * @since 101.0.0
 */
interface BatchSizeManagementInterface
{
    /**
     * Ensure memory size for data in batch.
     *
     * @param AdapterInterface $adapter database adapter.
     * @param int $batchSize
     * @return void
     * @since 101.0.0
     */
    public function ensureBatchSize(\Magento\Framework\DB\Adapter\AdapterInterface $adapter, $batchSize);
}
