<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
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
     * @var SetDataToLegacyStockItem
     */
    private $setDataToLegacyStockItem;

    /**
     * @var BulkZeroLegacyStockItem
     */
    private $bulkZeroLegacyStockItem;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param BulkZeroLegacyStockItem $bulkZeroLegacyStockItem
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        BulkZeroLegacyStockItem $bulkZeroLegacyStockItem
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->bulkZeroLegacyStockItem = $bulkZeroLegacyStockItem;
    }

    /**
     * @param string $sku
     * @param string $source
     * @return array|null
     */
    private function getSourceItemData(string $sku, string $source): ?array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $query = $connection->select()->from($tableName)
            ->where(SourceItemInterface::SOURCE_CODE . ' = ?', $source)
            ->where(SourceItemInterface::SKU . ' = ?', $sku);

        $res = $connection->fetchRow($query);
        if ($res === false) {
            return null;
        }

        return $res;
    }

    /**
     * @param string $sku
     * @param string $originSource
     * @param string $destinationSource
     * @return void
     */
    private function transferInventory(
        string $sku,
        string $originSource,
        string $destinationSource
    ): void {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $orgSourceItem = $this->getSourceItemData($sku, $originSource);
        $dstSourceItem = $this->getSourceItemData($sku, $destinationSource);

        $orgSourceItemQty = $orgSourceItem === null ? 0.0 : (float) $orgSourceItem[SourceItemInterface::QUANTITY];
        $dstSourceItemQty = $dstSourceItem === null ? 0.0 : (float) $dstSourceItem[SourceItemInterface::QUANTITY];

        $finalQuantity = $orgSourceItemQty + $dstSourceItemQty;

        if ($orgSourceItem !== null) {
            $status = (int) $orgSourceItem[SourceItemInterface::STATUS];
        } elseif ($dstSourceItemQty !== null) {
            $status = (int) $dstSourceItem[SourceItemInterface::STATUS];
        } else {
            $status = (int) SourceItemInterface::STATUS_OUT_OF_STOCK;
        }

        $updateOperation = [
            SourceItemInterface::QUANTITY => $finalQuantity,
            SourceItemInterface::STATUS => $status,
        ];

        if ($dstSourceItem === null) {
            $updateOperation[SourceItemInterface::SOURCE_CODE] = $destinationSource;
            $updateOperation[SourceItemInterface::SKU] = $sku;

            $connection->insert($tableName, $updateOperation);
        } elseif ($orgSourceItem !== null) {
            $connection->update($tableName, $updateOperation, [
                SourceItemInterface::SOURCE_CODE . '=?' => $destinationSource,
                SourceItemInterface::SKU . '=?' => $sku,
            ]);
        }

        // Align legacy stock
        if ($destinationSource === $this->defaultSourceProvider->getCode()) {
            $this->setDataToLegacyStockItem->execute($sku, $finalQuantity, $status);
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

        $connection->beginTransaction();
        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                $this->transferInventory((string)$sku, $originSource, $destinationSource);
                $processedSkus[] = $sku;
            }
        }

        if (!empty($processedSkus)) {
            $this->clearSource($processedSkus, $originSource, $unassignFromOrigin);
        }

        $connection->commit();
    }
}
