<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\IsProductSalable;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class IsNotManageStock
 */
class IsNotManageStock implements IsProductSalableInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param StockConfigurationInterface $configuration
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        StockConfigurationInterface $configuration,
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
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $globalManageStock = $this->configuration->getManageStock();
        $manageStock = false;
        if ((
                $stockItemConfiguration->getUseConfigManageStock() == 1 &&
                $globalManageStock == 1
            ) || (
                $stockItemConfiguration->getUseConfigManageStock() == 0 &&
                $stockItemConfiguration->getManageStock() == 1
            )
        ) {
            $manageStock = true;
        }

        return !$manageStock;
    }
}
