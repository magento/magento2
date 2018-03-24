<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\InventoryReservations;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Api\Data\ReservationInterface;

/**
 * Prevent append reservation if use_config_manage_stock is set to 0
 */
class PreventAppendReservationOnUseConfigManageStockSetToZeroPlugin
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(GetStockItemConfigurationInterface $getStockItemConfiguration)
    {
        $this->getStockItemConfiguration = $getStockItemConfiguration;
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
