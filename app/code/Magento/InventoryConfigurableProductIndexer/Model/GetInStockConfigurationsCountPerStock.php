<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Model;

class GetInStockConfigurationsCountPerStock
{
    /**
     * @param string $configurableSku
     * @return array
     */
    public function execute(string $configurableSku): array
    {
        return [
            20 => 0
        ];
    }
}
