<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;

/**
 * @inheritdoc
 */
class StockItemDataCondition implements IsProductSalableInterface
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->getStockItemData = $getStockItemData;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        if (null === $stockItemData) {
            // Sku is not assigned to Stock
            return false;
        }

        return (bool)$stockItemData['is_salable'];
    }
}
