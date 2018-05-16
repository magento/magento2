<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\IsProductSalableCondition;

use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

/**
 * @inheritdoc
 */
class IsSalableWithReservationsCondition implements IsProductSalableInterface
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypesBySkus
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        if (null === $stockItemData) {
            // Sku is not assigned to Stock
            return false;
        }

        $productType = $this->getProductTypesBySkus->execute([$sku])[$sku];
        if (false === $this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            return (bool)$stockItemData[GetStockItemDataInterface::IS_SALABLE];
        }

        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $qtyWithReservation = $stockItemData[GetStockItemDataInterface::QUANTITY] +
            $this->getReservationsQuantity->execute($sku, $stockId);
        return (bool)$stockItemData[GetStockItemDataInterface::IS_SALABLE] &&
            $qtyWithReservation > $stockItemConfiguration->getMinQty();
    }
}
