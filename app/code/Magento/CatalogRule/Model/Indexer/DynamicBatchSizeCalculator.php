<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

/**
 * Calculates optimal batch sizes for PHP memory-bound operations
 */
class DynamicBatchSizeCalculator
{
    /**
     * Percentage of memory limit to use for attribute caching
     */
    private const ATTRIBUTE_CACHE_MEMORY_PERCENTAGE = 0.40;

    /**
     * Estimated memory per product for attribute data (bytes)
     */
    private const MEMORY_PER_PRODUCT_ATTRIBUTE = 2048;

    /**
     * Minimum batch size
     */
    private const MIN_BATCH_SIZE = 500;

    /**
     * Maximum batch size
     */
    private const MAX_BATCH_SIZE = 50000;

    /**
     * Minimum batches in memory
     */
    private const MIN_BATCHES_IN_MEMORY = 2;

    /**
     * Maximum batches in memory
     */
    private const MAX_BATCHES_IN_MEMORY = 100;

    /**
     * @var int|null
     */
    private $memoryLimit;

    /**
     * @var array
     */
    private $calculatedSizes = [];

    /**
     * Get memory limit in bytes
     *
     * @return int
     */
    private function getMemoryLimit(): int
    {
        if ($this->memoryLimit === null) {
            $memoryLimit = ini_get('memory_limit');

            if ($memoryLimit === '-1') {
                $this->memoryLimit = 2 * 1024 * 1024 * 1024;
            } else {
                $this->memoryLimit = $this->convertToBytes($memoryLimit);
            }
        }

        return $this->memoryLimit;
    }

    /**
     * Convert PHP memory limit notation to bytes
     *
     * @param string $value
     * @return int
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int)substr($value, 0, -1);

        switch ($unit) {
            case 'g':
                return $number * 1024 * 1024 * 1024;
            case 'm':
                return $number * 1024 * 1024;
            case 'k':
                return $number * 1024;
            default:
                return (int)$value;
        }
    }

    /**
     * Get available memory for operations (excluding Magento base usage)
     *
     * @return int
     */
    private function getAvailableMemory(): int
    {
        $totalMemory = $this->getMemoryLimit();
        $currentUsage = memory_get_usage(true);
        $magentoBaseOverhead = 400 * 1024 * 1024;

        $available = $totalMemory - $currentUsage - $magentoBaseOverhead;

        return max($available, 100 * 1024 * 1024);
    }

    /**
     * Calculate optimal batch size for attribute loading
     *
     * @return int
     */
    public function getAttributeBatchSize(): int
    {
        if (isset($this->calculatedSizes['attribute_batch_size'])) {
            return $this->calculatedSizes['attribute_batch_size'];
        }

        $availableMemory = $this->getAvailableMemory();
        $memoryForAttributes = $availableMemory * self::ATTRIBUTE_CACHE_MEMORY_PERCENTAGE;

        $maxBatchesInMemory = $this->getMaxBatchesInMemory();
        $memoryPerBatch = $memoryForAttributes / $maxBatchesInMemory;

        $batchSize = (int)($memoryPerBatch / self::MEMORY_PER_PRODUCT_ATTRIBUTE);

        $batchSize = max(self::MIN_BATCH_SIZE, min(self::MAX_BATCH_SIZE, $batchSize));

        $this->calculatedSizes['attribute_batch_size'] = $batchSize;

        return $batchSize;
    }

    /**
     * Calculate maximum number of batches to keep in memory
     *
     * @return int
     */
    public function getMaxBatchesInMemory(): int
    {
        if (isset($this->calculatedSizes['max_batches'])) {
            return $this->calculatedSizes['max_batches'];
        }

        $availableMemory = $this->getAvailableMemory();
        $memoryForAttributes = $availableMemory * self::ATTRIBUTE_CACHE_MEMORY_PERCENTAGE;

        $estimatedBatchMemory = self::MIN_BATCH_SIZE * self::MEMORY_PER_PRODUCT_ATTRIBUTE;
        $maxBatches = (int)($memoryForAttributes / $estimatedBatchMemory);

        $maxBatches = max(self::MIN_BATCHES_IN_MEMORY, min(self::MAX_BATCHES_IN_MEMORY, $maxBatches));

        $this->calculatedSizes['max_batches'] = $maxBatches;

        return $maxBatches;
    }
}
