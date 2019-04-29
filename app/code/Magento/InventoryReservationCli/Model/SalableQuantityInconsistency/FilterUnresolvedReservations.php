<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\SalableQuantityInconsistency;

/**
 * Remove all compensated reservations
 */
class FilterUnresolvedReservations
{
    /**
     * Remove all compensated reservations
     * @param SalableQuantityInconsistency[] $inconsistencies
     * @return SalableQuantityInconsistency[]
     */
    public function execute(array $inconsistencies): array
    {
        foreach ($inconsistencies as $inconsistency) {
            $inconsistency->setItems(array_filter($inconsistency->getItems()));
        }

        return array_filter(
            $inconsistencies,
            function (SalableQuantityInconsistency $inconsistency) {
                return count($inconsistency->getItems()) > 0;
            }
        );
    }
}
