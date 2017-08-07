<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Generator of consecutive entity ID ranges that must be handled as a batch.
 *
 * Can be used during indexation process to split large amount of data into batches
 * and process them one by one in order to reduce memory consumption and improve overall performance.
 *
 * @api retrieve Batches when implementing custom Indexer\Action
 * @since 2.2.0
 */
interface BatchProviderInterface
{
    /**
     * Retrieve batches (entity ID ranges) from the given table.
     *
     * @param AdapterInterface $adapter database adapter.
     * @param string $tableName target table name.
     * @param string $linkField field that is used as a record identifier.
     * @param int $batchSize size of the single range.
     * @return \Generator generator that produces entity ID ranges in the format of ['from' => ..., 'to' => ...]
     * @since 2.2.0
     */
    public function getBatches(AdapterInterface $adapter, $tableName, $linkField, $batchSize);

    /**
     * Get list of entity ids based on batch
     *
     * @param AdapterInterface $connection
     * @param Select $select
     * @param array $batch
     * @return array
     * @since 2.2.0
     */
    public function getBatchIds(AdapterInterface $connection, Select $select, array $batch);
}
