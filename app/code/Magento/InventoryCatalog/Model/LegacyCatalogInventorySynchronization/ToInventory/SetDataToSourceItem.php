<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\ToLegacyCatalogInventory;

use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\GetLegacyStockItemsByProductIds;
use Magento\InventoryCatalog\Model\UpdateSourceItemBasedOnLegacyStockItem;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

class SetDataToSourceItem
{
    /**
     * @var UpdateSourceItemBasedOnLegacyStockItem
     */
    private $updateSourceItemBasedOnLegacyStockItem;

    /**
     * @var GetLegacyStockItemsByProductIds
     */
    private $getLegacyStockItemsByProductIds;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * SetDataToSourceItem constructor.
     * @param UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem
     * @param GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem,
        GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->updateSourceItemBasedOnLegacyStockItem = $updateSourceItemBasedOnLegacyStockItem;
        $this->getLegacyStockItemsByProductIds = $getLegacyStockItemsByProductIds;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @param array $skus
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $skus): void
    {
        $productIds = $this->getProductIdsBySkus->execute($skus);
        $stockItems = $this->getLegacyStockItemsByProductIds->execute($productIds);

        foreach ($stockItems as $stockItem) {
            $this->updateSourceItemBasedOnLegacyStockItem->execute($stockItem);
        }
    }
}
