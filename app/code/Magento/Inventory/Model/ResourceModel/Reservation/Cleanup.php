<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\Reservation;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Setup\Operation\CreateReservationTable;
use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * The resource model responsible for clearing reservation table (if needed) to prevent overloading, finding complete
 * pairs of reservations, that is, those (grouped by stock and product) whose sum is 0.
 *
 * @package Magento\Inventory\Model\ResourceModel\Reservation
 */
class Cleanup
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Cleanup constructor.
     *
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $connection = $this->resource->getConnection();
        $reservationTableName = $connection->getTableName(CreateReservationTable::TABLE_NAME_RESERVATION);

        $select = $connection->select()
            ->from($reservationTableName, ['grouped_reservation_ids' => 'group_concat(' . ReservationInterface::RESERVATION_ID . ')'])
            ->group([ReservationInterface::STOCK_ID, ReservationInterface::SKU])
            ->having('sum(' . ReservationInterface::QUANTITY . ') = 0');
        $groupedReservationIds = $connection->fetchCol($select, 'grouped_reservation_ids');

        foreach ($groupedReservationIds as $reservationIds) {
            $condition = [ReservationInterface::RESERVATION_ID . ' IN (?)' => explode(',', $reservationIds)];
            $connection->delete($reservationTableName, $condition);
        }
    }
}
