<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * @inheritdoc
 */
class ManageStockCondition implements IsProductSalableInterface
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
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

        $globalManageStock = $this->configuration->getManageStock();
        $manageStock = false;
        if ((
                $stockItemConfiguration->isUseConfigManageStock() == 1 &&
                $globalManageStock == 1
            ) || (
                $stockItemConfiguration->isUseConfigManageStock() == 0 &&
                $stockItemConfiguration->isManageStock() == 1
            )
        ) {
            $manageStock = true;
        }

        return !$manageStock;
    }
}
