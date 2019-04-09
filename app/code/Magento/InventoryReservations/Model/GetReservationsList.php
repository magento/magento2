<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model;

use Magento\InventoryReservations\Model\ResourceModel\GetListReservations;

class GetReservationsList
{
    /**
     * @var GetListReservations
     */
    private $getListReservations;

    /**
     * @param GetListReservations $getListReservations
     */
    public function __construct (
        GetListReservations $getListReservations
    ) {
        $this->getListReservations = $getListReservations;
    }

    /**
     * @return array
     */
    public function getListReservationsTotOrder(): array
    {
        return $this->getListReservations->execute();
    }
}
