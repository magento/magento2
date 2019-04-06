<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class GetOrderWithBrokenReservation
{
    /**
     * @var GetReservationsTotOrder
     */
    private $getReservationsTotOrder;
    /**
     * @var GetOrderInFinalState
     */
    private $getOrderInFinalState;

    /**
     * GetOrderWithBrokenReservation constructor.
     * @param GetReservationsTotOrder $getReservationsTotOrder
     * @param GetOrderInFinalState $getOrderInFinalState
     */
    public function __construct(
        GetReservationsTotOrder $getReservationsTotOrder,
        GetOrderInFinalState $getOrderInFinalState
    ) {
        $this->getReservationsTotOrder = $getReservationsTotOrder;
        $this->getOrderInFinalState = $getOrderInFinalState;
    }

    /**
     * @return OrderInterface[]
     */
    public function execute(): array
    {
        /** @var array $orderListReservations */
        $orderListReservations = $this->getReservationsTotOrder->getListReservationsTotOrder();

        $brokenReservation = array_column($orderListReservations, 'ReservationTot', 'OrderId');
        $orderIds = array_keys($brokenReservation);
        /** @var Collection $orders */
        $orders = $this->getOrderInFinalState->execute($orderIds);
        return $orders->getItems();
    }
}
