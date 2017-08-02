<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Composite batch size calculator for EAV related indexers.
 *
 * Can be configured to provide batch sizes for different indexer types.
 * @since 2.2.0
 */
class BatchSizeCalculator
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $batchSizes;

    /**
     * @var \Magento\Framework\Indexer\BatchSizeManagement[]
     * @since 2.2.0
     */
    private $batchSizeManagers;

    /**
     * @param array $batchSizes preferable sizes (number of rows in batch) of batches per index type
     * @param array $batchSizeManagers batch managers per index type
     * @since 2.2.0
     */
    public function __construct(
        array $batchSizes,
        array $batchSizeManagers
    ) {
        $this->batchSizes = $batchSizes;
        $this->batchSizeManagers = $batchSizeManagers;
    }

    /**
     * Estimate batch size and ensure that database will be able to handle it properly.
     *
     * @param AdapterInterface $connection
     * @param string $indexerTypeId unique identifier of the indexer
     * @return int estimated batch size
     * @throws NoSuchEntityException thrown if indexer identifier is not recognized
     * @since 2.2.0
     */
    public function estimateBatchSize(
        AdapterInterface $connection,
        $indexerTypeId
    ) {
        if (!isset($this->batchSizes[$indexerTypeId])) {
            throw NoSuchEntityException::singleField('indexTypeId', $indexerTypeId);
        }
        $this->batchSizeManagers[$indexerTypeId]->ensureBatchSize($connection, $this->batchSizes[$indexerTypeId]);

        return $this->batchSizes[$indexerTypeId];
    }
}
