<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\Api\Data;

class StockItemInterfacePlugin
{
    /**
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $subject
     * @param callable $proceed
     * @return int
     */
    public function aroundGetMinQty(\Magento\CatalogInventory\Api\Data\StockItemInterface $subject, callable $proceed)
    {
        $originalMinQty = $proceed();

        if ($subject->getBackorders()) {
            return $originalMinQty >= 0 ? 0 : $originalMinQty;
        }

        return $originalMinQty > 0 ? $originalMinQty : 0;
    }
}
