<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacySynchronization\ToInventory;

use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\InventoryCatalog\Model\UpdateSourceItemBasedOnLegacyStockItem;

class SetDataToSourceItem
{
    /**
     * @var UpdateSourceItemBasedOnLegacyStockItem
     */
    private $updateSourceItemBasedOnLegacyStockItem;

    /**
     * @var StockItemInterfaceFactory
     */
    private $stockItemInterfaceFactory;

    /**
     * SetDataToSourceItem constructor.
     * @param UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem
     * @param StockItemInterfaceFactory $stockItemInterfaceFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem,
        StockItemInterfaceFactory $stockItemInterfaceFactory
    ) {
        $this->updateSourceItemBasedOnLegacyStockItem = $updateSourceItemBasedOnLegacyStockItem;
        $this->stockItemInterfaceFactory = $stockItemInterfaceFactory;
    }

    /**
     * @param array $legacyItemsData
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $legacyItemsData): void
    {
        foreach ($legacyItemsData as $legacyItemData) {
            $stockItem = $this->stockItemInterfaceFactory->create(['data' => $legacyItemData]);
            $this->updateSourceItemBasedOnLegacyStockItem->execute($stockItem);
        }
    }
}
