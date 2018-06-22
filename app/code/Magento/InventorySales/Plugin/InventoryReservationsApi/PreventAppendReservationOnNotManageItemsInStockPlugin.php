<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryReservationsApi;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Prevent append reservation if use_config_manage_stock is set to 0
 */
class PreventAppendReservationOnNotManageItemsInStockPlugin
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param AppendReservationsInterface $subject
     * @param \Closure $proceed
     * @param ReservationInterface[] $reservations
     *
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(AppendReservationsInterface $subject, \Closure $proceed, array $reservations)
    {
        if (!$this->stockConfiguration->canSubtractQty()) {
            return;
        }

        $reservationToAppend = [];
        foreach ($reservations as $reservation) {
            $stockItemConfiguration = $this->getStockItemConfiguration->execute(
                $reservation->getSku(),
                $reservation->getStockId()
            );

            if ($stockItemConfiguration->isManageStock()) {
                $reservationToAppend[] = $reservation;
            }
        }

        if (!empty($reservationToAppend)) {
            $proceed($reservationToAppend);
        }
    }
}
