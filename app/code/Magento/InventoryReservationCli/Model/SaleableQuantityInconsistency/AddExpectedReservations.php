<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\GetOrdersInNotFinalState;

/**
 * Add expected reservations by current incomplete orders
 */
class AddExpectedReservations
{
    /**
     * @var GetOrdersInNotFinalState
     */
    private $getOrdersInNotFinalState;

    /**
     * @param GetOrdersInNotFinalState $getOrdersInNotFinalState
     */
    public function __construct(
        GetOrdersInNotFinalState $getOrdersInNotFinalState
    ) {
        $this->getOrdersInNotFinalState = $getOrdersInNotFinalState;
    }

    /**
     * Add expected reservations by current incomplete orders
     * @param Collector $collector
     */
    public function execute(Collector $collector): void
    {
        foreach ($this->getOrdersInNotFinalState->execute() as $order) {
            foreach ($order->getItems() as $item) {
                $collector->add((int)$order->getEntityId(), $item->getSku(), (float)$item->getQtyOrdered(), $order);
            }
        }
    }
}
