<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Model\DisableLegacyStockItemByDefaultSourceItem;
use Magento\InventoryCatalog\Model\DisableLegacyStockStatusByDefaultSourceItem;

/**
 * Plugin help to delete related entries from the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item if deleted source item is default source item.
 */
class DeleteLegacyCatalogInventoryPlugin
{
    /**
     * @var DisableLegacyStockItemByDefaultSourceItem
     */
    private $disableStockItemBySourceItem;

    /**
     * @var DisableLegacyStockStatusByDefaultSourceItem
     */
    private $disableStockStatusBySourceItem;

    /**
     * @param DisableLegacyStockItemByDefaultSourceItem $disableStockItemBySourceItem
     * @param DisableLegacyStockStatusByDefaultSourceItem $disableStockStatusBySourceItem
     */
    public function __construct(
        DisableLegacyStockItemByDefaultSourceItem $disableStockItemBySourceItem,
        DisableLegacyStockStatusByDefaultSourceItem $disableStockStatusBySourceItem
    ) {
        $this->disableStockItemBySourceItem = $disableStockItemBySourceItem;
        $this->disableStockStatusBySourceItem = $disableStockStatusBySourceItem;
    }

    /**
     * Plugin method to delete entry from the legacy tables.
     *
     * @param SourceItemsDeleteInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     *
     * @see SourceItemsDeleteInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterExecute(SourceItemsDeleteInterface $subject, $result, array $sourceItems)
    {
        foreach ($sourceItems as $sourceItem) {
            $this->disableStockItemBySourceItem->execute($sourceItem);
            $this->disableStockStatusBySourceItem->execute($sourceItem);
        }
    }
}
