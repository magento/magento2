<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\UpdateLegacyStockItemByDefaultSourceItem;
use Magento\InventoryCatalog\Model\UpdateLegacyStockStatusByDefaultSourceItem;

/**
 * Plugin help to fill the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item to don't break the backward compatible.
 */
class UpdateLegacyCatalogInventoryOnSourceItemsSavePlugin
{
    /**
     * @var UpdateLegacyStockItemByDefaultSourceItem
     */
    private $updateLegacyStockItem;

    /**
     * @var UpdateLegacyStockStatusByDefaultSourceItem
     */
    private $updateLegacyStockStatus;

    /**
     * @param UpdateLegacyStockItemByDefaultSourceItem $updateLegacyStockItem
     * @param UpdateLegacyStockStatusByDefaultSourceItem $updateLegacyStockStatus
     */
    public function __construct(
        UpdateLegacyStockItemByDefaultSourceItem $updateLegacyStockItem,
        UpdateLegacyStockStatusByDefaultSourceItem $updateLegacyStockStatus
    ) {
        $this->updateLegacyStockItem = $updateLegacyStockItem;
        $this->updateLegacyStockStatus = $updateLegacyStockStatus;
    }

    /**
     * Plugin method to updates cataloginventory_stock_item and cataloginventory_stock_status qty
     *
     * @param SourceItemsSaveInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     *
     * @see SourceItemsSaveInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(SourceItemsSaveInterface $subject, $result, array $sourceItems)
    {
        foreach ($sourceItems as $sourceItem) {
            $this->updateLegacyStockItem->execute($sourceItem);
            $this->updateLegacyStockStatus->execute($sourceItem);
        }
    }
}
