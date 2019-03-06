<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization;

/**
 * Set Qty and status for legacy CatalogInventory Stock Item table
 */
class AlignLegacyCatalogInventoryByProducts
{
    /**
     * @var IsAsyncLegacyAlignment
     */
    private $isAsyncLegacyAlignment;

    /**
     * @var SynchronousSetDataToLegacyCatalogInventory
     */
    private $synchronousSetDataToLegacyCatalogInventory;

    /**
     * @var AsynchronousSetDataToLegacyCatalogInventory
     */
    private $asynchronousSetDataToLegacyCatalogInventory;

    /**
     * AlignLegacyCatalogInventoryByProducts constructor.
     * @param IsAsyncLegacyAlignment $isAsyncLegacyAlignment
     * @param SynchronousSetDataToLegacyCatalogInventory $synchronousSetDataToLegacyCatalogInventory
     * @param AsynchronousSetDataToLegacyCatalogInventory $asynchronousSetDataToLegacyCatalogInventory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        IsAsyncLegacyAlignment $isAsyncLegacyAlignment,
        SynchronousSetDataToLegacyCatalogInventory $synchronousSetDataToLegacyCatalogInventory,
        AsynchronousSetDataToLegacyCatalogInventory $asynchronousSetDataToLegacyCatalogInventory
    ) {
        $this->isAsyncLegacyAlignment = $isAsyncLegacyAlignment;
        $this->synchronousSetDataToLegacyCatalogInventory = $synchronousSetDataToLegacyCatalogInventory;
        $this->asynchronousSetDataToLegacyCatalogInventory = $asynchronousSetDataToLegacyCatalogInventory;
    }

    /**
     * @param array $skus
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $skus): void
    {
        if ($this->isAsyncLegacyAlignment->execute()) {
            $this->asynchronousSetDataToLegacyCatalogInventory->execute($skus);
        } else {
            $this->synchronousSetDataToLegacyCatalogInventory->execute($skus);
        }
    }
}
