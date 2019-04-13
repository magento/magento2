<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\GetOrdersInFinalState;

/**
 * Match completed orders with unresolved reservations
 */
class AddCompletedOrdersToUnresolved
{
    /**
     * @var GetOrdersInFinalState
     */
    private $getOrdersInFinalState;

    /**
     * @param GetOrdersInFinalState $getOrdersInFinalState
     */
    public function __construct(
        GetOrdersInFinalState $getOrdersInFinalState
    ) {
        $this->getOrdersInFinalState = $getOrdersInFinalState;
    }

    /**
     * Remove all entries without order
     * @param Collector $collector
     */
    public function execute(Collector $collector): void
    {
        $inconsistencies = $collector->getInconsistencies();

        $orderIds = [];
        foreach ($inconsistencies as $inconsistency) {
            $orderIds[] = $inconsistency->getObjectId();
        }

        foreach ($this->getOrdersInFinalState->execute($orderIds) as $order) {
            if (isset($inconsistencies[$order->getEntityId()])) {
                $inconsistencies[$order->getEntityId()]->setOrder($order);
            }
        }

        $collector->setInconsistencies($inconsistencies);
    }
}
