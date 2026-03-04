<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;

/**
 * Migrate related catalog category and product tables for single store view mode
 */
class CatalogCategoryAndProductResolverOnSingleStoreMode
{
    /**
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Process the Catalog and Product tables and migrate to single store view mode
     *
     * @param int $storeId
     * @param string $table
     * @return void
     * @throws CouldNotSaveException
     */
    private function process(int $storeId, string $table): void
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $catalogProductTable = $this->resourceConnection->getTableName($table);

        $catalogProducts = $this->getCatalogProducts($table, $linkField, $storeId);
        $linkFieldIds = [];
        $attributeIds = [];
        $valueIds = [];
        try {
            if ($catalogProducts) {
                foreach ($catalogProducts as $catalogProduct) {
                    $linkFieldIds[] = $catalogProduct[$linkField];
                    $attributeIds[] = $catalogProduct[AttributeInterface::ATTRIBUTE_ID];
                    $valueIds[] = $catalogProduct['value_id'];
                }
                $this->massDelete($catalogProductTable, $linkField, $attributeIds, $linkFieldIds);
                $this->massUpdate($catalogProductTable, $valueIds);
            }
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        }
    }

    /**
     * Migrate catalog category and product tables
     *
     * @param int $storeId
     * @throws Exception
     */
    public function migrateCatalogCategoryAndProductTables(int $storeId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tables = [
            'catalog_category_entity_datetime',
            'catalog_category_entity_decimal',
            'catalog_category_entity_int',
            'catalog_category_entity_text',
            'catalog_category_entity_varchar',
            'catalog_product_entity_datetime',
            'catalog_product_entity_decimal',
            'catalog_product_entity_gallery',
            'catalog_product_entity_int',
            'catalog_product_entity_text',
            'catalog_product_entity_varchar',
        ];
        try {
            $connection->beginTransaction();
            foreach ($tables as $table) {
                $this->process($storeId, $table);
            }
            $connection->commit();
        } catch (Exception $exception) {
            $connection->rollBack();
        }
    }

    /**
     * Delete default store related products
     *
     * @param string $catalogProductTable
     * @param string $linkField
     * @param array $attributeIds
     * @param array $linkFieldIds
     * @return void
     */
    private function massDelete(
        string $catalogProductTable,
        string $linkField,
        array $attributeIds,
        array $linkFieldIds
    ): void {
        $connection = $this->resourceConnection->getConnection();

        $connection->delete(
            $catalogProductTable,
            [
                'store_id = ?' => Store::DEFAULT_STORE_ID,
                AttributeInterface::ATTRIBUTE_ID. ' IN(?)' => $attributeIds,
                $linkField.' IN(?)' => $linkFieldIds
            ]
        );
    }

    /**
     * Update default store related products
     *
     * @param string $catalogProductTable
     * @param array $valueIds
     * @return void
     */
    private function massUpdate(string $catalogProductTable, array $valueIds): void
    {
        $connection = $this->resourceConnection->getConnection();

        $connection->update(
            $catalogProductTable,
            ['store_id' => Store::DEFAULT_STORE_ID],
            ['value_id IN(?)' => $valueIds]
        );
    }

    /**
     * Get list of products
     *
     * @param string $table
     * @param string $linkField
     * @param int $storeId
     * @return array
     */
    private function getCatalogProducts(string $table, string $linkField, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $catalogProductTable = $this->resourceConnection->getTableName($table);
        $select = $connection->select()
            ->from($catalogProductTable, ['value_id', AttributeInterface::ATTRIBUTE_ID, $linkField])
            ->where('store_id = ?', $storeId);
        return $connection->fetchAll($select);
    }
}
