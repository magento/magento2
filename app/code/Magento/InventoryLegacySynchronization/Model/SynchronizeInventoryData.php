<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model;

use Magento\InventoryLegacySynchronization\Model\ToMsi\SetDataToSourceItem;
use Magento\InventoryLegacySynchronization\Model\ToLegacy\SetDataToLegacyInventory;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class SynchronizeInventoryData
{
    /**
     * @var SetDataToLegacyInventory
     */
    private $setDataToLegacyInventory;

    /**
     * @var SetDataToSourceItem
     */
    private $setDataToSourceItem;

    /**
     * SetDataToDestination constructor.
     * @param SetDataToLegacyInventory $setDataToLegacyInventory
     * @param SetDataToSourceItem $setDataToSourceItem
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SetDataToLegacyInventory $setDataToLegacyInventory,
        SetDataToSourceItem $setDataToSourceItem
    ) {
        $this->setDataToLegacyInventory = $setDataToLegacyInventory;
        $this->setDataToSourceItem = $setDataToSourceItem;
    }

    /**
     * @param string $destination
     * @param array $items
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $destination, array $items): void
    {
        if ($destination === Synchronize::MSI_TO_LEGACY) {
            $this->setDataToLegacyInventory->execute($items);
        } elseif ($destination === Synchronize::LEGACY_TO_MSI) {
            $this->setDataToSourceItem->execute($items);
        }
    }
}
