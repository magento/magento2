<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\Command\UpdateCatalogInventoryStockItemByDefaultSourceItem;
use Magento\InventoryCatalog\Model\Command\UpdateCatalogInventoryStockStatusByDefaultSourceItem;

/**
 * Plugin help to fill the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item to don't break the backward compatible.
 */
class SaveStockItemsOnSourceItemsSavePlugin
{
    /**
     * @var UpdateCatalogInventoryStockItemByDefaultSourceItem
     */
    private $updateLegacyStockItem;

    /**
     * @var UpdateCatalogInventoryStockStatusByDefaultSourceItem
     */
    private $updateLegacyStockStatus;

    /**
     * @param UpdateCatalogInventoryStockItemByDefaultSourceItem $updateLegacyStockItem
     * @param UpdateCatalogInventoryStockStatusByDefaultSourceItem $updateLegacyStockStatus
     */
    public function __construct(
        UpdateCatalogInventoryStockItemByDefaultSourceItem $updateLegacyStockItem,
        UpdateCatalogInventoryStockStatusByDefaultSourceItem $updateLegacyStockStatus
    ) {
        $this->updateLegacyStockItem = $updateLegacyStockItem;
        $this->updateLegacyStockStatus = $updateLegacyStockStatus;
    }

    /**
     * Plugin method to fill the legacy tables.
     *
     * @param SourceItemsSaveInterface $subject
     * @param void $result
     * @param ReservationInterface[] $reservations
     *
     * @see SourceItemsSaveInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(SourceItemsSaveInterface $subject, $result, array $reservations)
    {
        $this->updateStockItemAndStatusTable($reservations);
    }

    /**
     * Updates cataloginventory_stock_item and cataloginventory_stock_status qty with reservation information.
     *
     * @param SourceItemInterface[] $sourceItems
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateStockItemAndStatusTable(array $sourceItems)
    {
        foreach ($sourceItems as $sourceItem) {
            $this->updateLegacyStockItem->execute($sourceItem);
            $this->updateLegacyStockStatus->execute($sourceItem);
        }
    }
}
