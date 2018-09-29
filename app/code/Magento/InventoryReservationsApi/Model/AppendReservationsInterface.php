<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationsApi\Model;

use Magento\InventoryReservationsApi\Model\ReservationInterface;

/**
 * Domain service used to append Reservations to keep track of quantity deductions before the related SourceItems
 * are updated.
 *
 * Some use cases are:
 *
 * - an Order is placed, completed or canceled
 * - an Order is split or partially refunded
 * - an RMA is placed or canceled
 *
 * @api
 */
interface AppendReservationsInterface
{
    /**
     * Append reservations
     *
     * @param ReservationInterface[] $reservations
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $reservations): void;
}
