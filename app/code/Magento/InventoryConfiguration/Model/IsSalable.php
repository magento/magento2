<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventoryCatalog\Model\GetLegacyStockItem;

/**
 * Class IsSalable
 * @package Magento\InventoryConfiguration\Model
 */
class IsSalable implements StockItemConditionInterface
{
    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * IsSalable constructor.
     * @param GetLegacyStockItem $getLegacyStockItem
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(
        GetLegacyStockItem $getLegacyStockItem,
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->getLegacyStockItem = $getLegacyStockItem;
        $this->getStockItemData = $getStockItemData;
    }

    /**
     * @param string $sku
     * @param int $stockItem
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function match(string $sku, int $stockItem): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku,$stockItem);
        $legacyStockItem = $this->getLegacyStockItem->execute($sku);
        $isSalable = (bool)$stockItemData['is_salable'];
        if (null === $legacyStockItem) {
            return $isSalable;
        }
        return false;
    }
}