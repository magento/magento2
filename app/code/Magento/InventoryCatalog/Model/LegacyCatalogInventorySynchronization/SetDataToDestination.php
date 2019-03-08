<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization;

use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\ToLegacyCatalogInventory\SetDataToLegacyInventory;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class SetDataToDestination
{
    /**
     * @var SetDataToLegacyInventory
     */
    private $setDataToLegacyInventory;

    /**
     * SetDataToDestination constructor.
     * @param SetDataToLegacyInventory $setDataToLegacyInventory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SetDataToLegacyInventory $setDataToLegacyInventory
    ) {
        $this->setDataToLegacyInventory = $setDataToLegacyInventory;
    }

    /**
     * @param string $direction
     * @param array $skus
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $direction, array $skus): void
    {
        if ($direction === Synchronize::DIRECTION_TO_LEGACY) {
            $this->setDataToLegacyInventory->execute($skus);
        }
    }
}
