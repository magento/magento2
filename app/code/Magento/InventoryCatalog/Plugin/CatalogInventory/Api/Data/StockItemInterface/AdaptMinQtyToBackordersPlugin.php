<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Api\Data\StockItemInterface;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class AdaptMinQtyToBackordersPlugin
{
    /**
     * @param StockItemInterface $subject
     * @param callable $proceed
     * @return int
     */
    public function aroundGetMinQty(StockItemInterface $subject, callable $proceed)
    {
        $originalMinQty = $proceed();

        if ($subject->getBackorders()) {
            return $originalMinQty >= 0 ? 0 : $originalMinQty;
        }

        return $originalMinQty > 0 ? $originalMinQty : 0;
    }
}
