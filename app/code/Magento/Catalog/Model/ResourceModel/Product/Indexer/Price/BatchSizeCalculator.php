<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;

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
     * @var DeploymentConfig|null
     */
    private $deploymentConfig;

    /**
     * Deployment config path
     *
     * @var string
     */
    private const DEPLOYMENT_CONFIG_INDEXER_BATCHES = 'indexer/batch_size/';

    /**
     * @param array $batchRowsCount
     * @param array $estimators
     * @param array $batchSizeAdjusters
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        array $batchRowsCount,
        array $estimators,
        array $batchSizeAdjusters,
        ?DeploymentConfig $deploymentConfig = null
    ) {
        $this->batchRowsCount = $batchRowsCount;
        $this->estimators = $estimators;
        $this->batchSizeAdjusters = $batchSizeAdjusters;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
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
        $batchRowsCount = $this->deploymentConfig->get(
            self::DEPLOYMENT_CONFIG_INDEXER_BATCHES . Processor::INDEXER_ID . '/' . $indexerTypeId,
            $batchRowsCount = $this->deploymentConfig->get(
                self::DEPLOYMENT_CONFIG_INDEXER_BATCHES . Processor::INDEXER_ID . '/' . 'default'
            )
        );

        if (is_null($batchRowsCount)) {
            $batchRowsCount = isset($this->batchRowsCount[$indexerTypeId])
                ? $this->batchRowsCount[$indexerTypeId]
                : $this->batchRowsCount['default'];
        }

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
