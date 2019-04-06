<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\InventoryReservations\Model\ResourceModel\GetListReservationsTotOrder;

class GetReservationsTotOrder
{
    /**
     * @var GetListReservationsTotOrder
     */
    private $getListReservationsTotOrder;

    /**
     * GetReservationsTotOrder constructor.
     * @param GetListReservationsTotOrder $getListReservationsTotOrder
     */
    public function __construct (
        GetListReservationsTotOrder $getListReservationsTotOrder
    ) {
        $this->getListReservationsTotOrder = $getListReservationsTotOrder;
    }

    /**
     * @return array
     */
    public function getListReservationsTotOrder(): array
    {
        return $this->getListReservationsTotOrder->execute();
    }
}
