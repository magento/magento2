<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class IsBackOrderAvailable
 */
class IsBackOrderAvailable implements IsProductSalableInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * IsNotManageStock constructor.
     *
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
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

        if ($stockItemConfiguration->getBackorders() !== StockItemConfigurationInterface::BACKORDERS_NO) {
            return true;
        }

        return false;
    }
}
