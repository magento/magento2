<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class IsBackOrderAvailable
 */
class IsBackOrderAvailable implements IsProductSalableInterface
{
    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

    /**
     * IsNotManageStock constructor.
     *
     * @param GetLegacyStockItem $getLegacyStockItem
     */
    public function __construct(
        GetLegacyStockItem $getLegacyStockItem
    ) {
        $this->getLegacyStockItem = $getLegacyStockItem;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool
    {
        $legacyStockItem = $this->getLegacyStockItem->execute($sku);

        if ($legacyStockItem->getBackorders() !== StockItemConfigurationInterface::BACKORDERS_NO) {
            return true;
        }

        return false;
    }
}
