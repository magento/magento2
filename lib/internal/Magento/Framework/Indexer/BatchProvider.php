<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

use \Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Generator of consecutive entity ID ranges that must be handled as a batch.
 *
 * Some ranges may contain non existent entity IDs.
 * So the code that uses the generator must check if any entities were loaded during batch load.
 */
class BatchProvider implements BatchProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getBatches(AdapterInterface $adapter, $tableName, $linkField, $batchSize)
    {
        $maxLinkFieldValue = $adapter->fetchOne(
            $adapter->select()->from(
                ['entity' => $tableName],
                [
                    'max_value' => new \Zend_Db_Expr('MAX(entity.' . $linkField . ')')
                ]
            )
        );

        /** @var int $truncatedBatchSize size of the last batch that is smaller than expected batch size */
        $truncatedBatchSize = $maxLinkFieldValue % $batchSize;
        /** @var int $fullBatchCount count of the batches that have expected batch size */
        $fullBatchCount = ($maxLinkFieldValue - $truncatedBatchSize) / $batchSize;

        for ($batchIndex = 0; $batchIndex < $fullBatchCount; $batchIndex ++) {
            yield ['from' => $batchIndex * $batchSize + 1, 'to' => ($batchIndex + 1) * $batchSize];
        }
        // return the last batch if it has smaller size
        if ($truncatedBatchSize > 0) {
            yield ['from' => $fullBatchCount * $batchSize + 1, 'to' => $maxLinkFieldValue];
        }
    }

    /**
     * @inheritdoc
     */
    public function getBatchIds(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        \Magento\Framework\DB\Select $select,
        array $batch
    ) {
        $betweenCondition = sprintf(
            '(%s BETWEEN %s AND %s)',
            'entity_id',
            $connection->quote($batch['from']),
            $connection->quote($batch['to'])
        );

        $ids = $connection->fetchCol($select->where($betweenCondition)->limit($batch['to'] - $batch['from'] + 1));
        return array_map('intval', $ids);
    }
}
