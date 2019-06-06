<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface;

class TransferInventoryPartially
{
    /** @var ResourceConnection  */
    private $resourceConnection;

    /** @var DefaultSourceProviderInterface  */
    private $defaultSourceProvider;

    /** @var SetDataToLegacyStockItem  */
    private $setDataToLegacyStockItemCommand;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyCatalogInventoryCommand
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyCatalogInventoryCommand
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setDataToLegacyStockItemCommand = $setDataToLegacyCatalogInventoryCommand;
    }

    /**
     * @param PartialInventoryTransferItemInterface $transfer
     * @param string $originSourceCode
     * @param string $destinationSourceCode
     */
    public function execute(PartialInventoryTransferItemInterface $transfer, string $originSourceCode, string $destinationSourceCode): void
    {
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        $originSourceItemData = $this->getSourceItemData($transfer->getSku(), $originSourceCode);
        $destSourceItemData = $this->getSourceItemData($transfer->getSku(), $destinationSourceCode);

        $updatedQtyAtOrigin = $originSourceItemData === null ? 0.0 : (float) $originSourceItemData[SourceItemInterface::QUANTITY] - $transfer->getQty();
        $updatedQtyAtDest = $destSourceItemData === null ? 0.0 : (float) $destSourceItemData[SourceItemInterface::QUANTITY] + $transfer->getQty();

        $originUpdate = [SourceItemInterface::QUANTITY => $updatedQtyAtOrigin];
        $destUpdate = [SourceItemInterface::QUANTITY => $updatedQtyAtDest, SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK];

        $connection->update($tableName, $originUpdate, [
            SourceItemInterface::SOURCE_CODE . '=?' => $originSourceCode,
            SourceItemInterface::SKU . '=?' => $transfer->getSku(),
        ]);
        $connection->update($tableName, $destUpdate, [
            SourceItemInterface::SOURCE_CODE . '=?' => $destinationSourceCode,
            SourceItemInterface::SKU . '=?' => $transfer->getSku(),
        ]);

        if ($originSourceCode === $this->defaultSourceProvider->getCode()) {
            $this->setDataToLegacyStockItemCommand->execute($transfer->getSku(), $updatedQtyAtOrigin, $originSourceItemData[SourceItemInterface::STATUS]);
        } elseif ($destinationSourceCode === $this->defaultSourceProvider->getCode()) {
            $this->setDataToLegacyStockItemCommand->execute($transfer->getSku(), $updatedQtyAtDest, SourceItemInterface::STATUS_IN_STOCK);
        }

        $connection->commit();
    }

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
}