<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\InventoryReservations\Model\GetReservationsQuantityInterface;
use Magento\CatalogInventory\Model\Configuration;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * @inheritdoc
 */
class MinQtyStockCondition implements IsProductSalableInterface
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
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        if (null === $stockItemConfiguration) {
            return false;
        }

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        $qtyWithReservation = $stockItemData[GetStockItemDataInterface::QUANTITY] +
            $this->getReservationsQuantity->execute($sku, $stockId);
        $globalMinQty = $this->configuration->getMinQty();

        if ((
            $stockItemConfiguration->isUseConfigMinQty() == 1 &&
            $qtyWithReservation <= $globalMinQty
            ) || (
                $stockItemConfiguration->isUseConfigMinQty() == 0 &&
                $qtyWithReservation <= $stockItemConfiguration->getMinQty()
            )
        ) {
            return false;
        }

        return true;
    }
}
