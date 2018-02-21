<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservations\Model\GetReservationsQuantityInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Class ConfigMinQty
 */
class ConfigMinQty implements IsProductSalableInterface
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * ConfigMinQty constructor.
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param Configuration $configuration
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        Configuration $configuration,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->configuration = $configuration;
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $sku, int $stockId): bool
    {
        /** @var StockItemConfigurationInterface $StockItemConfiguration */
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $qtyWithReservation = $stockItemData['quantity'] + $this->getReservationsQuantity->execute($sku, $stockId);
        $globalMinQty = $this->configuration->getMinQty();

        if (($stockItemConfiguration->getUseConfigMinQty() == 1 && $qtyWithReservation <= $globalMinQty)
            || ($stockItemConfiguration->getUseConfigMinQty() == 0 && $qtyWithReservation <= $stockItemConfiguration->getMinQty())
        ) {
            return false;
        }

        return true;
    }
}
