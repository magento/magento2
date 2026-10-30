<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\BatchSizeManagementInterface;

/**
 * Calculate and validate batch size for catalogrule insert operations
 */
class CatalogRuleInsertBatchSizeCalculator
{
    /**
     * Default batch size for insert operations
     */
    private const DEFAULT_BATCH_SIZE = 5000;

    /**
     * @var BatchSizeManagementInterface
     */
    private $batchSizeManagement;

    /**
     * @var int
     */
    private $defaultBatchSize;

    /**
     * @param BatchSizeManagementInterface $batchSizeManagement
     * @param int $defaultBatchSize
     */
    public function __construct(
        BatchSizeManagementInterface $batchSizeManagement,
        int $defaultBatchSize = self::DEFAULT_BATCH_SIZE
    ) {
        $this->batchSizeManagement = $batchSizeManagement;
        $this->defaultBatchSize = $defaultBatchSize;
    }

    /**
     * Retrieve validated batch size for insert operations
     *
     * @param AdapterInterface $connection
     * @return int
     */
    public function getInsertBatchSize(AdapterInterface $connection): int
    {
        $batchSize = $this->defaultBatchSize;

        $this->batchSizeManagement->ensureBatchSize($connection, $batchSize);

        return (int)$batchSize;
    }
}
