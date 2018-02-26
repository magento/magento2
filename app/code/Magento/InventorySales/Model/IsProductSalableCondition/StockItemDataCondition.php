<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

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
        return (bool)$stockItemData['is_salable'];
    }
}
