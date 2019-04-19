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
class FilterUnresolvedReservations
{
    /**
     * Remove all compensated reservations
     * @param SaleableQuantityInconsistency[] $inconsistencies
     * @return SaleableQuantityInconsistency[]
     */
    public function execute(array $inconsistencies): array
    {
        foreach ($inconsistencies as $inconsistency) {
            $inconsistency->setItems(array_filter($inconsistency->getItems()));
        }

        return array_filter(
            $inconsistencies,
            function (SaleableQuantityInconsistency $inconsistency) {
                return count($inconsistency->getItems()) > 0;
            }
        );
    }
}
