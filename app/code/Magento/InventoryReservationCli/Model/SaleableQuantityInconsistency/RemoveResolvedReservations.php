<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

/**
 * Remove all compensated reservations
 */
class RemoveResolvedReservations
{
    /**
     * Remove all compensated reservations
     * @param Collector $collector
     */
    public function execute(Collector $collector): void
    {
        foreach ($collector->getInconsistencies() as $inconsistency) {
            $inconsistency->setItems(array_filter($inconsistency->getItems()));
        }

        $collector->setInconsistencies(array_filter(
            $collector->getInconsistencies(),
            function (SaleableQuantityInconsistency $inconsistency) {
                return count($inconsistency->getItems()) > 0;
            }
        ));
    }
}
