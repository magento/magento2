<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Implementation of bulk source assignment
 *
 * This class is not intended to be used directly.
 * @see \Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface
 */
class BulkInventoryTransfer
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var BulkZeroLegacyStockItem
     */
    private $bulkZeroLegacyStockItem;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem @deprecated
     * @param BulkZeroLegacyStockItem $bulkZeroLegacyStockItem
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        BulkZeroLegacyStockItem $bulkZeroLegacyStockItem,
        GetProductIdsBySkusInterface $getProductIdsBySkus = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->bulkZeroLegacyStockItem = $bulkZeroLegacyStockItem;
        $this->getProductIdsBySkus =
            $getProductIdsBySkus ?: ObjectManager::getInstance()->get(GetProductIdsBySkusInterface::class);
    }

    /**
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @return void
     */
    private function transferInventory(
        array $skus,
        string $originSource,
        string $destinationSource
    ): void {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $defaultSourceDestination = ($destinationSource === $this->defaultSourceProvider->getCode());

        $orgSourceItemsData = $connection->fetchAssoc(
            $connection->select()
                ->from($tableName, [SourceItemInterface::SKU, '*'])
                ->where(SourceItemInterface::SOURCE_CODE . ' = ?', $originSource)
                ->where(SourceItemInterface::SKU . ' IN (?)', $skus)
        );
        $dstSourceItemsData = $connection->fetchAssoc(
            $connection->select()
                ->from($tableName, [SourceItemInterface::SKU, '*'])
                ->where(SourceItemInterface::SOURCE_CODE . ' = ?', $destinationSource)
                ->where(SourceItemInterface::SKU . ' IN (?)', $skus)
        );

        $productIds = [];
        if ($defaultSourceDestination) {
            $productIds = $this->getProductIdsBySkus->execute($skus);
        }

        $finalSourceItemsData = [];
        $legacySyncData = [];
        foreach ($skus as $sku) {
            $finalQuantity = (float)
                ($orgSourceItemsData[$sku][SourceItemInterface::QUANTITY] ?? 0.0) +
                ($dstSourceItemsData[$sku][SourceItemInterface::QUANTITY] ?? 0.0);

            $finalStatus = $orgSourceItemsData[$sku][SourceItemInterface::STATUS] ??
                $dstSourceItemsData[$sku][SourceItemInterface::STATUS] ??
                SourceItemInterface::STATUS_OUT_OF_STOCK;

            $finalSourceItemsData[] = [
                SourceItemInterface::SOURCE_CODE => $destinationSource,
                SourceItemInterface::SKU => $sku,
                SourceItemInterface::QUANTITY => $finalQuantity,
                SourceItemInterface::STATUS => $finalStatus,
            ];

            if (isset($productIds[$sku]) && $defaultSourceDestination) {
                $legacySyncData[] = [
                    StockItemInterface::QTY => $finalQuantity,
                    StockItemInterface::IS_IN_STOCK => $finalStatus,
                    StockItemInterface::PRODUCT_ID => $productIds[$sku],
                    'website_id' => 0
                ];
            }
        }

        $connection->insertOnDuplicate(
            $tableName,
            $finalSourceItemsData,
            [
                SourceItemInterface::QUANTITY,
                SourceItemInterface::STATUS
            ]
        );

        if ($defaultSourceDestination) {
            $connection->insertOnDuplicate( // Mass update via insert on duplicate
                $this->resourceConnection->getTableName('cataloginventory_stock_item'),
                $legacySyncData,
                [
                    StockItemInterface::QTY,
                    StockItemInterface::IS_IN_STOCK,
                ]
            );
        }
    }

    /**
     * @param string[] $skus
     * @param string $source
     * @param bool $unassign
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function clearSource(array $skus, string $source, bool $unassign)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        if ($unassign) {
            $connection->delete($tableName, [
                SourceItemInterface::SOURCE_CODE . '=?' => $source,
                SourceItemInterface::SKU . ' IN(?)' => $skus,
            ]);
        } else {
            $connection->update($tableName, [
                SourceItemInterface::QUANTITY => 0,
                SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
            ], [
                SourceItemInterface::SOURCE_CODE . '=?' => $source,
                SourceItemInterface::SKU . ' IN(?)' => $skus,
            ]);
        }

        // Align legacy stock
        if ($source === $this->defaultSourceProvider->getCode()) {
            $this->bulkZeroLegacyStockItem->execute($skus);
        }
    }

    /**
     * Assign sources to products
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @param bool $unassignFromOrigin
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignFromOrigin
    ): void {
        $connection = $this->resourceConnection->getConnection();
        $types = $this->getProductTypesBySkus->execute($skus);
        $processedSkus = [];

        $filteredSkus = [];
        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                $filteredSkus[] = $sku;
            }
        }

        if (empty($filteredSkus)) {
            return;
        }

        $connection->beginTransaction();

        $this->transferInventory($filteredSkus, $originSource, $destinationSource);
        $this->clearSource($filteredSkus, $originSource, $unassignFromOrigin);

        $connection->commit();
    }
}
