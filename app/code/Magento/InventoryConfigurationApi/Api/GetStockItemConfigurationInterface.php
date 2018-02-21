<?php
/**
 * Created by PhpStorm.
 * User: furman
 * Date: 21.02.18
 * Time: 12:00
 */

namespace Magento\InventoryConfigurationApi\Api;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;

interface GetStockItemConfigurationInterface
{
    public function execute(string $sku, int $stockId): StockItemConfigurationInterface;
}