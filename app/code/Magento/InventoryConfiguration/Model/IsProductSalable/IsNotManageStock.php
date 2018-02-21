<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\IsProductSalable;

use Magento\CatalogInventory\Model\Configuration;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
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
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param Configuration $configuration
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        Configuration $configuration,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->configuration = $configuration;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool
    {
        /** @var StockItemConfigurationInterface $StockItemConfiguration */
        $StockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $globalManageStock = $this->configuration->getManageStock();
        $manageStock = false;
        if (($StockItemConfiguration->getUseConfigManageStock() == 1 && $globalManageStock == 1)
            || ($StockItemConfiguration->getUseConfigManageStock() == 0 && $StockItemConfiguration->getManageStock() == 1)
        ) {
            $manageStock = true;
        }

        return !$manageStock;
    }
}
