<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

/**
 * Remove all reservations without matching order
 */
class FilterExistingOrders
{
    /**
     * Remove all reservations without matching order
     *
     * @param SaleableQuantityInconsistency[] $inconsistencies
     * @return SaleableQuantityInconsistency[]
     */
    public function execute(array $inconsistencies): array
    {
        return array_filter(
            $inconsistencies,
            function (SaleableQuantityInconsistency $inconsistency) {
                return (bool)$inconsistency->getOrder();
            }
        );
    }
}
