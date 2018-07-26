<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\Spi\StockStateProvider;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;

class AdaptVerifyStockToNegativeMinQtyPlugin
{
    /**
     * @param StockStateProviderInterface $subject
     * @param bool $result
     * @param StockItemInterface $stockItem
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterVerifyStock(StockStateProviderInterface $subject, bool $result, StockItemInterface $stockItem)
    {
        if ($stockItem->getBackorders() !== StockItemInterface::BACKORDERS_NO
            && $stockItem->getMinQty() < 0
            && $stockItem->getQty() < $stockItem->getMinQty()
        ) {
            return false;
        }
        return $result;
    }
}
