<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Product;

use Magento\CatalogInventory\Api\StockRegistryInterface;

class QuantityValidator
{
    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        private readonly StockRegistryInterface $stockRegistry
    ) {
    }

    /**
     * To get quantity validators
     *
     * @param int $productId
     * @param int|null $websiteId
     *
     * @return array
     */
    public function getData(int $productId, int|null $websiteId): array
    {
        $stockItem = $this->stockRegistry->getStockItem($productId, $websiteId);

        if (!$stockItem) {
            return [];
        }

        $params = [];
        $validators = [];
        $params['minAllowed'] =  $stockItem->getMinSaleQty();
        if ($stockItem->getMaxSaleQty()) {
            $params['maxAllowed'] = $stockItem->getMaxSaleQty();
        }
        if ($stockItem->getQtyIncrements() > 0) {
            $params['qtyIncrements'] = (float) $stockItem->getQtyIncrements();
        }
        $validators['validate-item-quantity'] = $params;

        return $validators;
    }
}
