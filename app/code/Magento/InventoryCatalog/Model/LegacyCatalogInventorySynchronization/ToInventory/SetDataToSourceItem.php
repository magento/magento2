<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\ToInventory;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\GetLegacyStockItemsByProductIds;
use Magento\InventoryCatalog\Model\UpdateSourceItemBasedOnLegacyStockItem;

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
     * @var Product
     */
    private $productResource;

    /**
     * SetDataToSourceItem constructor.
     * @param UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem
     * @param GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds
     * @param Product $productResource
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem,
        GetLegacyStockItemsByProductIds $getLegacyStockItemsByProductIds,
        Product $productResource
    ) {
        $this->updateSourceItemBasedOnLegacyStockItem = $updateSourceItemBasedOnLegacyStockItem;
        $this->productResource = $productResource;
        $this->getLegacyStockItemsByProductIds = $getLegacyStockItemsByProductIds;
    }

    /**
     * @param array $skus
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(array $skus): void
    {
        $productIds = $this->productResource->getProductsIdsBySkus($skus);
        if (!empty($productIds)) {
            $stockItems = $this->getLegacyStockItemsByProductIds->execute($productIds);

            foreach ($stockItems as $stockItem) {
                $this->updateSourceItemBasedOnLegacyStockItem->execute($stockItem);
            }
        }
    }
}
