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
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockStatus;

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
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQuantityInStock;

    /**
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param SetDataToLegacyStockStatus $setDataToLegacyStockStatus
     * @param GetProductQuantityInStockInterface $getProductQuantityInStock
     * @param IsProductInStockInterface $isProductInStock
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        DefaultStockProviderInterface $defaultStockProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        SetDataToLegacyStockStatus $setDataToLegacyStockStatus,
        GetProductQuantityInStockInterface $getProductQuantityInStock,
        IsProductInStockInterface $isProductInStock
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->setDataToLegacyStockStatus = $setDataToLegacyStockStatus;
        $this->getProductQuantityInStock = $getProductQuantityInStock;
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
        // TODO: 'https://github.com/magento-engcom/msi/issues/368'
        return;
        if ($this->stockConfiguration->canSubtractQty()) {
            foreach ($reservations as $reservation) {
                if ($this->defaultStockProvider->getId() !== $reservation->getStockId()) {
                    continue;
                }
                $qty = $this->getProductQuantityInStock->execute($reservation->getSku(), $reservation->getStockId());
                $status = (int)$this->isProductInStock->execute($reservation->getSku(), $reservation->getStockId());

                $this->setDataToLegacyStockItem->execute($reservation->getSku(), $qty, $status);
                $this->setDataToLegacyStockStatus->execute($reservation->getSku(), $qty, $status);
            }
        }
    }
}
