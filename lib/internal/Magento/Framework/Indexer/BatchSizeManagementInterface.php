<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use \Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Batch size manager can be used to ensure that MEMORY table has enough memory for data in batch.
 * @api
 * @since 2.2.0
 */
interface BatchSizeManagementInterface
{
    /**
     * Ensure memory size for data in batch.
     *
     * @param AdapterInterface $adapter database adapter.
     * @param int $batchSize
     * @return void
     * @since 2.2.0
     */
    public function ensureBatchSize(\Magento\Framework\DB\Adapter\AdapterInterface $adapter, $batchSize);
}
