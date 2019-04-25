<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

use Magento\InventoryReservationCli\Model\GetCompleteOrderStatusList;
use Magento\InventoryReservationCli\Model\SaleableQuantityInconsistency;

/**
 * Remove all reservations with incomplete state
 */
class FilterIncompleteOrders
{
    /**
     * @var GetCompleteOrderStatusList
     */
    private $getCompleteOrderStatusList;

    /**
     * @param GetCompleteOrderStatusList $getCompleteOrderStatusList
     */
    public function __construct(
        GetCompleteOrderStatusList $getCompleteOrderStatusList
    ) {
        $this->getCompleteOrderStatusList = $getCompleteOrderStatusList;
    }

    /**
     * Remove all reservations with incomplete state
     *
     * @param SaleableQuantityInconsistency[] $inconsistencies
     * @return SaleableQuantityInconsistency[]
     */
    public function execute(array $inconsistencies): array
    {
        return array_filter(
            $inconsistencies,
            function (SaleableQuantityInconsistency $inconsistency) {
                return !in_array($inconsistency->getOrder()->getStatus(), $this->getCompleteOrderStatusList->execute());
            }
        );
    }
}
