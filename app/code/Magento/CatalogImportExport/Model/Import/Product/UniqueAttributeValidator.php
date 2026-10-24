<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;

class UniqueAttributeValidator
{
    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @param MetadataPool $metadataPool
     * @param SkuStorage $skuStorage
     */
    public function __construct(
        private readonly MetadataPool $metadataPool,
        private readonly SkuStorage $skuStorage
    ) {
    }

    /**
     * Check if provided value is unique for the attribute
     *
     * @param Product $context
     * @param string $attributeCode
     * @param string $sku
     * @param string $value
     * @return bool
     * @throws \Exception
     */
    public function isValid(Product $context, string $attributeCode, string $sku, string $value): bool
    {
        $cacheKey = strtolower($attributeCode);
        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = $this->load($context, $attributeCode);
        }
        $entityData = $this->skuStorage->get($sku);
        $id = null;
        if ($entityData !== null) {
            $id = $entityData[$this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()];
        }
        return !isset($this->cache[$cacheKey][$value]) || in_array($id, $this->cache[$cacheKey][$value]);
    }

    /**
     * Load attribute values with corresponding entity ids
     *
     * @param Product $context
     * @param string $attributeCode
     * @return array
     * @throws LocalizedException
     */
    private function load(Product $context, string $attributeCode): array
    {
        /** @var AbstractAttribute $attributeObject */
        $attributeObject = $context->retrieveAttributeByCode($attributeCode);
        if ($attributeObject->isStatic()) {
            return [];
        }
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $connection = $context->getConnection();
        $idField = $metadata->getLinkField();
        $select = $connection->select()
            ->from(
                $attributeObject->getBackend()->getTable(),
                ['value', $idField]
            )
            ->where(
                'attribute_id = :attribute_id'
            );
        $result = [];
        foreach ($connection->fetchAll($select, ['attribute_id' => $attributeObject->getId()]) as $row) {
            $result[$row['value']][] = $row[$idField];
        }
        return $result;
    }

    /**
     * Clear cached attribute values
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
