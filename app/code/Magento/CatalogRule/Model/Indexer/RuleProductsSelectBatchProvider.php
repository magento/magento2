<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Generator;
use InvalidArgumentException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Convert\DataSize;
use Zend_Db_Statement_Interface;

/**
 * Execute rule-product SELECTs in batches of complete products.
 */
class RuleProductsSelectBatchProvider
{
    /**
     * Amortize query overhead; this throughput floor may exceed the soft row budget.
     */
    private const MIN_BATCH_SIZE = 500;

    /**
     * Bound IN-list/query load; a 5,000-ID benchmark showed no material throughput gain.
     */
    private const MAX_BATCH_SIZE = 2000;

    /**
     * Leave 60% of available PHP memory for variable downstream work.
     */
    private const RESULT_BUFFER_MEMORY_SHARE = 0.4;

    /**
     * A benchmark of 100k rows in the buffered result set showed 210 bytes per row on average.
     * This adds 22% margin over that average to account for additional overhead.
     */
    private const RESULT_BUFFER_BYTES_PER_ROW = 256;

    /**
     * Use the 756M baseline when memory_limit is unlimited.
     */
    private const UNLIMITED_MEMORY_BUDGET_BYTES = 756 * 1024 * 1024;

    /**
     * @var int|null
     */
    private ?int $memoryLimit = null;

    /**
     * @param IndexerTableSwapperInterface $tableSwapper
     * @param ResourceConnection $resource
     * @param DataSize $dataSize
     * @param RuleProductsSelectBuilder $ruleProductsSelectBuilder
     * @param int $minProductsPerBatch
     * @param int $maxProductsPerBatch
     * @param int $resultBufferBytesPerRow
     * @param float $resultBufferMemoryShare
     * @param int $unlimitedMemoryBudgetBytes
     */
    public function __construct(
        private readonly IndexerTableSwapperInterface $tableSwapper,
        private readonly ResourceConnection $resource,
        private readonly DataSize $dataSize,
        private readonly RuleProductsSelectBuilder $ruleProductsSelectBuilder,
        private readonly int $minProductsPerBatch = self::MIN_BATCH_SIZE,
        private readonly int $maxProductsPerBatch = self::MAX_BATCH_SIZE,
        private readonly int $resultBufferBytesPerRow = self::RESULT_BUFFER_BYTES_PER_ROW,
        private readonly float $resultBufferMemoryShare = self::RESULT_BUFFER_MEMORY_SHARE,
        private readonly int $unlimitedMemoryBudgetBytes = self::UNLIMITED_MEMORY_BUDGET_BYTES
    ) {
        if ($minProductsPerBatch < 1) {
            throw new InvalidArgumentException('minProductsPerBatch must be greater than zero.');
        }
        if ($maxProductsPerBatch < 1) {
            throw new InvalidArgumentException('maxProductsPerBatch must be greater than zero.');
        }
        if ($maxProductsPerBatch < $minProductsPerBatch) {
            throw new InvalidArgumentException(
                'maxProductsPerBatch must be greater than or equal to minProductsPerBatch.'
            );
        }
        if ($resultBufferBytesPerRow < 1) {
            throw new InvalidArgumentException('resultBufferBytesPerRow must be greater than zero.');
        }
        if (!is_finite($resultBufferMemoryShare)
            || $resultBufferMemoryShare <= 0
            || $resultBufferMemoryShare > 1
        ) {
            throw new InvalidArgumentException(
                'resultBufferMemoryShare must be finite, greater than zero and less than or equal to one.'
            );
        }

        if ($unlimitedMemoryBudgetBytes < 1) {
            throw new InvalidArgumentException('unlimitedMemoryBudgetBytes must be greater than zero.');
        }
    }

    /**
     * Yield executed SELECT statements, preserving complete products within each result.
     *
     * Null and empty product filters both mean all products, matching the underlying builder.
     * Consumers must close each cursor before requesting the next statement.
     *
     * @param int $websiteId
     * @param array|null $productIds
     * @param bool $useAdditionalTable
     * @return Generator<Zend_Db_Statement_Interface>
     */
    public function execute(
        int $websiteId,
        ?array $productIds = null,
        bool $useAdditionalTable = false
    ): Generator {
        $tableName = $this->resource->getTableName('catalogrule_product');
        if ($useAdditionalTable) {
            $tableName = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product')
            );
        }
        foreach ($this->getBatches($websiteId, $tableName, $productIds) as $batchProductIds) {
            $statement = $this->ruleProductsSelectBuilder->buildSelect(
                $websiteId,
                $batchProductIds,
                $useAdditionalTable
            );
            yield $statement;
            unset($statement);
        }
    }

    /**
     * Pack complete products with a soft row budget and configurable product-count floor.
     *
     * Only the final batch may contain fewer than the configured minimum products.
     *
     * @param int $websiteId
     * @param string $tableName
     * @param array|null $productIds
     * @return Generator<int[]>
     */
    private function getBatches(int $websiteId, string $tableName, ?array $productIds): Generator
    {
        $rowCapacity = $this->getRowCapacity();
        $batchProductIds = [];
        $rowCount = 0;
        foreach ($this->getProductRowCounts($websiteId, $tableName, $productIds) as $productId => $productRows) {
            if (count($batchProductIds) >= $this->maxProductsPerBatch
                || (count($batchProductIds) >= $this->minProductsPerBatch && $rowCount + $productRows > $rowCapacity)
            ) {
                yield $batchProductIds;
                $batchProductIds = [];
                $rowCount = 0;
            }

            $batchProductIds[] = $productId;
            $rowCount += $productRows;
        }

        if ($batchProductIds) {
            yield $batchProductIds;
        }
    }

    /**
     * Yield product IDs and their row counts, ordered by product ID.
     *
     * @param int $websiteId
     * @param string $tableName
     * @param array|null $productIds
     * @return Generator<int, int>
     */
    private function getProductRowCounts(int $websiteId, string $tableName, ?array $productIds): Generator
    {
        $connection = $this->resource->getConnection();
        $lastProductId = 0;
        do {
            $select = $connection->select()
                ->from($tableName, ['product_id', 'relation_rows' => new \Zend_Db_Expr('COUNT(*)')])
                ->where('website_id = ?', $websiteId)
                ->where('product_id > ?', $lastProductId)
                ->group('product_id')
                ->order('product_id')
                ->limit($this->maxProductsPerBatch);
            if (!empty($productIds)) {
                $select->where('product_id IN (?)', $productIds);
            }
            $products = $connection->fetchAll($select);
            foreach ($products as $product) {
                $lastProductId = (int)$product['product_id'];
                yield $lastProductId => (int)$product['relation_rows'];
            }
        } while (count($products) === $this->maxProductsPerBatch);
    }

    /**
     * Get the maximum number of rows that can be buffered in memory, based on the configured memory limit and row size.
     *
     * @return int
     */
    private function getRowCapacity(): int
    {
        $availableMemory = max(0, $this->getMemoryLimit() - memory_get_usage(true));
        $bufferBudget = $availableMemory * $this->resultBufferMemoryShare;

        return max(
            1,
            (int)floor($bufferBudget / $this->resultBufferBytesPerRow)
        );
    }

    /**
     * Get the memory limit in bytes
     *
     * @return int
     */
    private function getMemoryLimit(): int
    {
        if ($this->memoryLimit === null) {
            $memoryLimit = $this->dataSize->convertSizeToBytes((string)ini_get('memory_limit'));
            if ($memoryLimit === -1) {
                $memoryLimit = $this->unlimitedMemoryBudgetBytes;
            }
            $this->memoryLimit = (int)$memoryLimit;
        }

        return (int)$this->memoryLimit;
    }
}
