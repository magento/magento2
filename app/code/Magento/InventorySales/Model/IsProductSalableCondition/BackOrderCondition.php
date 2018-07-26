<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * @inheritdoc
 */
class BackOrderCondition implements IsProductSalableInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        if ($stockItemConfiguration->getBackorders() !== StockItemConfigurationInterface::BACKORDERS_NO
            && $stockItemConfiguration->getMinQty() >= 0) {
            return true;
        }

        return false;
    }
}
