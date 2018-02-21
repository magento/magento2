<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

class GetStockItemConfiguration
{
    public function execute(string $sku, int $stockId): StockItemConfigurationInterface
    {

    }
}
