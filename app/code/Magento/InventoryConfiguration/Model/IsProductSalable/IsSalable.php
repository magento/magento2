<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\IsProductSalable;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class IsSalable
 */
class IsSalable implements IsProductSalableInterface
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
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        return (bool)$stockItemData['is_salable'];
    }
}
