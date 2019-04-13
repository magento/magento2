<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

/**
 * Remove all entries without order
 */
class RemoveReservationsWithoutRelevantOrder
{
    /**
     * Remove all entries without order
     * @param Collector $collector
     */
    public function execute(Collector $collector): void
    {
        $collector->setInconsistencies(array_filter(
            $collector->getInconsistencies(),
            function (SaleableQuantityInconsistency $inconsistency) {
                return (bool)$inconsistency->getOrder();
            }
        ));
    }
}
