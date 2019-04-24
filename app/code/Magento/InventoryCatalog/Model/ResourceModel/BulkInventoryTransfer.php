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
     * @param ResourceConnection $resourceConnection
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
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

        $finalSourceItemsData = [];
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
        }

        $connection->insertOnDuplicate(
            $tableName,
            $finalSourceItemsData,
            [
                SourceItemInterface::QUANTITY,
                SourceItemInterface::STATUS
            ]
        );
    }

    /**
     * @param string[] $skus
     * @param string $source
     * @param bool $unassign
     */
    private function clearSource(array $skus, string $source, bool $unassign): void
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
    }

    /**
     * Assign sources to products
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @param bool $unassignFromOrigin
     * @return void
     */
    public function execute(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignFromOrigin
    ): void {
        $connection = $this->resourceConnection->getConnection();
        $types = $this->getProductTypesBySkus->execute($skus);

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
