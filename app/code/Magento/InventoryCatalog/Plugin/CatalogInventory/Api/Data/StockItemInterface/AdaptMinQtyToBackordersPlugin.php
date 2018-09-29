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
    public function afterGetMinQty(StockItemInterface $subject, float $result)
    {
        if ($subject->getBackorders()) {
            return $result >= 0 ? 0 : $result;
        }

        return $result > 0 ? $result : 0;
    }
}
