<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\ResourceModel\Product;

use Magento\CatalogRule\Model\Indexer\DynamicBatchSizeCalculator;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\LocalizedException;

/**
 * Lazy-loading attribute values container with bounded memory usage
 *
 * @implements \ArrayAccess<int, array<int, mixed>>
 * @implements \Countable
 */
class AttributeValuesLoader implements \ArrayAccess, \Countable
{
    /**
     * @var Collection
     */
    private Collection $collection;

    /**
     * @var AbstractAttribute
     */
    private AbstractAttribute $attribute;

    /**
     * @var DynamicBatchSizeCalculator
     */
    private $batchSizeCalculator;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var int
     */
    private $maxBatchesInMemory;

    /**
     * @var array Loaded data: entity_id => [store_id => value]
     */
    private $loadedData = [];

    /**
     * @var array Track loaded entity IDs in batches
     */
    private $loadedBatches = [];

    /**
     * @var array Queue of loaded batch start IDs for LRU eviction
     */
    private $batchQueue = [];

    /**
     * @var int|null Cached count
     */
    private $totalCount = null;

    /**
     * @param Collection $collection
     * @param AbstractAttribute $attribute
     * @param DynamicBatchSizeCalculator $batchSizeCalculator
     */
    public function __construct(
        Collection $collection,
        AbstractAttribute $attribute,
        DynamicBatchSizeCalculator $batchSizeCalculator
    ) {
        $this->collection = $collection;
        $this->attribute = $attribute;
        $this->batchSizeCalculator = $batchSizeCalculator;
        $this->batchSize = $batchSizeCalculator->getAttributeBatchSize();
        $this->maxBatchesInMemory = $batchSizeCalculator->getMaxBatchesInMemory();
    }

    /**
     * Check if entity has attribute values
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $entityId = (int)$offset;

        if (isset($this->loadedData[$entityId])) {
            return true;
        }

        $this->loadBatchForEntity($entityId);

        return isset($this->loadedData[$entityId]);
    }

    /**
     * Get attribute values for entity
     *
     * @param mixed $offset
     * @return array<int, mixed>|null
     */
    public function offsetGet($offset): ?array
    {
        $entityId = (int)$offset;

        if (!isset($this->loadedData[$entityId])) {
            $this->loadBatchForEntity($entityId);
        }

        return $this->loadedData[$entityId] ?? null;
    }

    /**
     * Set offset not supported
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetSet($offset, $value): void
    {
        throw new \LogicException('AttributeValuesLoader is read-only');
    }

    /**
     * Offset unset not supported
     *
     * @param mixed $offset
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($offset): void
    {
        throw new \LogicException('AttributeValuesLoader is read-only');
    }

    /**
     * Get total count of entities
     *
     * @return int
     */
    public function count(): int
    {
        if ($this->totalCount === null) {
            $this->totalCount = (int)$this->collection->getSize();
        }
        return $this->totalCount;
    }

    /**
     * Load batch containing the requested entity
     *
     * @param int $entityId
     * @return void
     */
    private function loadBatchForEntity(int $entityId): void
    {
        $batchStartId = (int)(floor($entityId / $this->batchSize) * $this->batchSize);

        if (isset($this->loadedBatches[$batchStartId])) {
            return;
        }

        $this->loadBatch($batchStartId);
        $this->evictOldBatchesIfNeeded();
    }

    /**
     * Load a specific batch of attribute values
     *
     * @param int $batchStartId
     * @return void
     * @throws LocalizedException|\Zend_Db_Statement_Exception
     */
    private function loadBatch(int $batchStartId): void
    {
        $attributeId = (int)$this->attribute->getId();
        $fieldMainTable = $this->collection->getConnection()->getAutoIncrementField(
            $this->collection->getMainTable()
        );
        $fieldJoinTable = $this->attribute->getEntity()->getLinkField();

        $select = $this->collection->getConnection()->select()
            ->from(['cpe' => $this->collection->getMainTable()], ['entity_id'])
            ->join(
                ['cpa' => $this->attribute->getBackend()->getTable()],
                'cpe.' . $fieldMainTable . ' = cpa.' . $fieldJoinTable,
                ['store_id', 'value']
            )
            ->where('attribute_id = ?', $attributeId)
            ->where('cpe.entity_id >= ?', $batchStartId)
            ->where('cpe.entity_id < ?', $batchStartId + $this->batchSize)
            ->order(['cpe.entity_id ASC', 'cpa.store_id ASC']);

        $stmt = $this->collection->getConnection()->query($select);

        while ($row = $stmt->fetch()) {
            $entityId = (int)$row['entity_id'];
            if (!isset($this->loadedData[$entityId])) {
                $this->loadedData[$entityId] = [];
            }
            $this->loadedData[$entityId][(int)$row['store_id']] = $row['value'];
        }

        unset($stmt);

        $this->loadedBatches[$batchStartId] = true;
        $this->batchQueue[] = $batchStartId;
    }

    /**
     * Evict old batches if memory limit exceeded
     *
     * @return void
     */
    private function evictOldBatchesIfNeeded(): void
    {
        while (count($this->loadedBatches) > $this->maxBatchesInMemory) {
            $oldestBatchStart = array_shift($this->batchQueue);
            unset($this->loadedBatches[$oldestBatchStart]);

            for ($id = $oldestBatchStart; $id < $oldestBatchStart + $this->batchSize; $id++) {
                unset($this->loadedData[$id]);
            }
        }
    }

    /**
     * Reset all loaded data and clear cache
     *
     * @return void
     */
    public function resetCache(): void
    {
        $this->loadedData = [];
        $this->loadedBatches = [];
        $this->batchQueue = [];
        $this->totalCount = null;
    }
}
