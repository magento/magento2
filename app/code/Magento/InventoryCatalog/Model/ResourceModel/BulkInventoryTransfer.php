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
     * @param string $sku
     * @param string $source
     * @return float|null
     */
    private function getQuantityFromSource(string $sku, string $source): ?float
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $query = $connection->select()->from($tableName, SourceItemInterface::QUANTITY)
            ->where(SourceItemInterface::SOURCE_CODE . ' = ?', $source)
            ->where(SourceItemInterface::SKU . ' = ?', $sku);

        $res = $connection->fetchOne($query);
        if ($res === false) {
            return null;
        }

        return (float) $res;
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

        $originQuantity = $this->getQuantityFromSource($sku, $originSource);
        $destinationQuantity = $this->getQuantityFromSource($sku, $destinationSource);

        $finalQuantity = (float) $originQuantity + (float) $destinationQuantity;

        if ($destinationQuantity === null) {
            $connection->insert($tableName, [
                SourceItemInterface::SOURCE_CODE => $destinationSource,
                SourceItemInterface::SKU => $sku,
                SourceItemInterface::QUANTITY => (float) $originQuantity,
                SourceItemInterface::STATUS => $finalQuantity > 0 ?
                    SourceItemInterface::STATUS_IN_STOCK :
                    SourceItemInterface::STATUS_OUT_OF_STOCK
            ]);
        } elseif ($originQuantity !== null) {
            $connection->update($tableName, [
                SourceItemInterface::QUANTITY => $finalQuantity
            ], [
                SourceItemInterface::SOURCE_CODE . '=?' => $destinationSource,
                SourceItemInterface::SKU . '=?' => $sku,
            ]);
        }
    }

    /**
     * @param string[] $skus
     * @param string $source
     * @param bool $unassign
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

        $connection->beginTransaction();
        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                $this->transferInventory($sku, $originSource, $destinationSource);
            }
        }

        $this->clearSource($skus, $originSource, $unassignFromOrigin);
        $connection->commit();
    }
}
