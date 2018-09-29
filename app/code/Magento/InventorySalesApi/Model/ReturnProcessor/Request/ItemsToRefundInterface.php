<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model\ReturnProcessor\Request;

/**
 * DTO used as the type for values of `$items` array passed to PlaceReservationsForSalesEventInterface::execute()
 * @see \Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface
 *
 */
interface ItemsToRefundInterface
{
    /**
     * @return string
     */
    public function getSku(): string;

    /**
     * @return float
     */
    public function getQuantity(): float;

    /**
     * @return float
     */
    public function getProcessedQuantity(): float;
}
