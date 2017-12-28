<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryApi\Api\Data\ReservationInterface;
use Magento\InventoryApi\Api\AppendReservationsInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalog\Model\ResourceModel\ApplyDataToLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\ApplyDataToLegacyStockStatus;
use Magento\InventoryApi\Api\IsProductInStockInterface;

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
     * @var ApplyDataToLegacyStockItem
     */
    private $applyDataToLegacyStockItem;

    /**
     * @var ApplyDataToLegacyStockStatus
     */
    private $applyDataToLegacyStockStatus;

    /**
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param ApplyDataToLegacyStockItem $applyDataToLegacyStockItem
     * @param ApplyDataToLegacyStockStatus $applyDataToLegacyStockStatus
     * @param IsProductInStockInterface $isProductInStock
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        DefaultStockProviderInterface $defaultStockProvider,
        ApplyDataToLegacyStockItem $applyDataToLegacyStockItem,
        ApplyDataToLegacyStockStatus $applyDataToLegacyStockStatus,
        IsProductInStockInterface $isProductInStock
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->applyDataToLegacyStockItem = $applyDataToLegacyStockItem;
        $this->applyDataToLegacyStockStatus = $applyDataToLegacyStockStatus;
        $this->isProductInStock = $isProductInStock;
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
                $this->applyDataToLegacyStockItem->execute($reservation->getSku(), (float)$reservation->getQuantity());
                $this->applyDataToLegacyStockStatus->execute(
                    $reservation->getSku(),
                    (float)$reservation->getQuantity(),
                    (int)$this->isProductInStock->execute($reservation->getSku(), $reservation->getStockId())
                );
            }
        }
    }
}
