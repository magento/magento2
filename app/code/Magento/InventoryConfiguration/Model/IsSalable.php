<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class IsSalable
 * @package Magento\InventoryConfiguration\Model
 */
class IsSalable implements IsProductSalableInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * IsSalable constructor.
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getStockItemData = $getStockItemData;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $isSalable = (bool)$stockItemData['is_salable'];
        if (null === $stockItemConfiguration) {
            return $isSalable;
        }

        return false;
    }
}
