<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\CatalogInventory\Model\Configuration;
use Magento\InventoryCatalog\Model\GetLegacyStockItem;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class IsNotManageStock
 */
class IsNotManageStock implements IsProductSalableInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var GetLegacyStockItem
     */
    private $getLegacyStockItem;

    /**
     * IsNotManageStock constructor.
     *
     * @param Configuration $configuration
     * @param GetLegacyStockItem $getLegacyStockItem
     */
    public function __construct(
        Configuration $configuration,
        GetLegacyStockItem $getLegacyStockItem
    ) {
        $this->getLegacyStockItem = $getLegacyStockItem;
        $this->configuration = $configuration;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool
    {
        $legacyStockItem = $this->getLegacyStockItem->execute($sku);
        $globalManageStock = $this->configuration->getManageStock();
        $manageStock = false;
        if (($legacyStockItem->getUseConfigManageStock() == 1 && $globalManageStock == 1)
            || ($legacyStockItem->getUseConfigManageStock() == 0 && $legacyStockItem->getManageStock() == 1)
        ) {
            $manageStock = true;
        }

        return !$manageStock;
    }
}
