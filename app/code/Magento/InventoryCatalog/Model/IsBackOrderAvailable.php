<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryConfiguration\Model\StockItemConditionInterface;
use Magento\CatalogInventory\Model\Stock;

/**
 * Class IsBackOrderAvailable
 * @package Magento\InventoryCatalog\Model
 */
class IsBackOrderAvailable implements StockItemConditionInterface
{
    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

    /**
     * IsNotManageStock constructor.
     * @param GetLegacyStockItem $getLegacyStockItem
     */
    public function __construct(
        GetLegacyStockItem $getLegacyStockItem
    ) {
        $this->getLegacyStockItem = $getLegacyStockItem;
    }

    /**
     * @param string $sku
     * @param int $stockItem
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function match(string $sku, int $stockItem): bool
    {
        $legacyStockItem = $this->getLegacyStockItem->execute($sku);
        if ($legacyStockItem->getBackorders() != Stock::BACKORDERS_NO)
            return true;
        return false;
    }
}
