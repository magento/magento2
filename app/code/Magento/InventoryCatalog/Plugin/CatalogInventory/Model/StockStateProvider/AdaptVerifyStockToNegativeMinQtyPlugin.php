<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\StockStateProvider;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider;

class AdaptVerifyStockToNegativeMinQtyPlugin
{
    /**
     * @param StockStateProvider $subject
     * @param bool $result
     * @param StockItemInterface $stockItem
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterVerifyStock(StockStateProvider $subject, bool $result, StockItemInterface $stockItem)
    {
        if ($stockItem->getQty() === null && $stockItem->getManageStock()) {
            return false;
        }
        if (($stockItem->getBackorders() === StockItemInterface::BACKORDERS_NO
                || $stockItem->getBackorders() !== StockItemInterface::BACKORDERS_NO
                && $stockItem->getMinQty() < 0)
            && $stockItem->getQty() <= $stockItem->getMinQty()
        ) {
            return false;
        }
        return true;
    }
}
