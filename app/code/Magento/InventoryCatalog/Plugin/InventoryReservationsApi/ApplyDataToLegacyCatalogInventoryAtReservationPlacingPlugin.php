<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryReservationsApi;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Api\Data\ReservationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockStatus;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * Apply inventory data changes  (qty, stock status) to legacy CatalogInventory (cataloginventory_stock_status and
 * cataloginventory_stock_item tables) at the time when Reservation(-s) have been appended using MSI APIs,
 * and these reservation(-s) correspond to Default Stock
 */
class ApplyDataToLegacyCatalogInventoryAtReservationPlacingPlugin
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var SetDataToLegacyStockItem
     */
    private $setDataToLegacyStockItem;

    /**
     * @var SetDataToLegacyStockStatus
     */
    private $setDataToLegacyStockStatus;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param SetDataToLegacyStockStatus $setDataToLegacyStockStatus
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param IsProductSalableInterface $isProductSalable
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        DefaultStockProviderInterface $defaultStockProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        SetDataToLegacyStockStatus $setDataToLegacyStockStatus,
        GetProductSalableQtyInterface $getProductSalableQty,
        IsProductSalableInterface $isProductSalable
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->setDataToLegacyStockStatus = $setDataToLegacyStockStatus;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->isProductSalable = $isProductSalable;
    }

    /**
     * @param AppendReservationsInterface $subject
     * @param void $result
     * @param ReservationInterface[] $reservations
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(AppendReservationsInterface $subject, $result, array $reservations)
    {
        if ($this->stockConfiguration->canSubtractQty()) {
            foreach ($reservations as $reservation) {
                if ($this->defaultStockProvider->getId() !== $reservation->getStockId()) {
                    continue;
                }
                $qty = $this->getProductSalableQty->execute($reservation->getSku(), $reservation->getStockId());
                $status = (int)$this->isProductSalable->execute($reservation->getSku(), $reservation->getStockId());

                $this->setDataToLegacyStockItem->execute($reservation->getSku(), $qty, $status);
                $this->setDataToLegacyStockStatus->execute($reservation->getSku(), $qty, $status);
            }
        }
    }
}
