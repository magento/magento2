<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\ResourceModel\ProductDataLoader;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Service loads all the SKUs from DB along with ids, attribute sets, types and stores it in memory efficient way
 */
class SkuStorage
{
    private const DELIMITER = '|';

    /**
     * @var MetadataPool
     */
    private MetadataPool $metadataPool;

    /**
     * @var array|null
     */
    private ?array $rows = null;

    /**
     * @var array
     */
    private array $typeIdMap = [];

    /**
     * @var array
     */
    private array $typeIdIndex = [];

    /**
     * @var string|null
     */
    private ?string $productEntityLinkField = null;

    /**
     * @var ProductDataLoader
     */
    private ProductDataLoader $productDataLoader;

    /**
     * @param MetadataPool $metadataPool
     * @param ProductDataLoader $productDataLoader
     */
    public function __construct(
        MetadataPool $metadataPool,
        ProductDataLoader $productDataLoader
    ) {
        $this->metadataPool = $metadataPool;
        $this->productDataLoader = $productDataLoader;
    }

    /**
     * Get product data by its SKU. SKU must be in lowercase
     *
     * @param string $key SKU
     * @return array|null
     */
    public function get(string $key): ?array
    {
        $this->init();
        if (!$this->has($key)) {
            return null;
        }
        $key = strtolower($key);

        return $this->unserialize($this->rows[$key]);
    }

    /**
     * Returns generator to iterate all the values in the storage
     *
     * @return \Generator
     */
    public function iterate(): \Generator
    {
        $this->init();
        foreach ($this->rows as $sku => $data) {
            yield $sku => $this->unserialize($data);
        }
    }

    /**
     * Checks does SKU exist in the list. SKU must be in lowercase
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->init();
        $key = strtolower($key);
        return isset($this->rows[$key]);
    }

    /**
     * Set product data to the list/update existing data
     *
     * @param array $data
     * @return void
     */
    public function set(array $data): void
    {
        $this->init();
        $this->rows[strtolower($data['sku'])] = implode(self::DELIMITER, [
            $data['entity_id'],
            $data[$this->getProductEntityLinkField()],
            $this->maskTypeId($data['type_id']),
            $data['attribute_set_id']
        ]);
    }

    /**
     * Completely resets the sku storage
     *
     * @return void
     */
    public function reset(): void
    {
        $this->rows = null;
        $this->init();
    }

    /**
     * Initialises sku list
     *
     * @return void
     */
    private function init(): void
    {
        if ($this->rows !== null) {
            return;
        }
        $this->rows = [];

        $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $linkedField = $this->getProductEntityLinkField();
        $columns = ['entity_id', 'type_id', 'attribute_set_id', 'sku'];
        if ($linkedField != $productMetadata->getIdentifierField()) {
            $columns[] = $linkedField;
        }

        foreach ($this->productDataLoader->getProductsData($columns) as $row) {
            $this->set($row);
        }
    }

    /**
     * Replaces string representation of product type with generated int ID
     *
     * @param string $typeIdString
     * @return int
     */
    private function maskTypeId(string $typeIdString): int
    {
        if (!isset($this->typeIdMap[$typeIdString])) {
            $this->typeIdIndex[] = $typeIdString;
            $this->typeIdMap[$typeIdString] = count($this->typeIdIndex) - 1;
        }

        return $this->typeIdMap[$typeIdString];
    }

    /**
     * Restores string representation of product type by their generated ID
     *
     * @param int $typeIdInt
     * @return string
     */
    private function unmaskTypeId(int $typeIdInt): string
    {
        return $this->typeIdIndex[$typeIdInt];
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField(): string
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        }
        return $this->productEntityLinkField;
    }

    /**
     * Convert serialized string into array with end values
     *
     * @param string $data
     * @return array
     */
    private function unserialize(string $data): array
    {
        $data = explode(self::DELIMITER, $data);

        return [
            'entity_id' => $data[0],
            $this->getProductEntityLinkField() => $data[1],
            'type_id' => $this->unmaskTypeId((int)$data[2]),
            'attr_set_id' => $data[3]
        ];
    }
}
