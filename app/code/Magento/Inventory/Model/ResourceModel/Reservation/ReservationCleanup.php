<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\Reservation;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ReservationCleanupInterface;
use Magento\Inventory\Setup\Operation\CreateReservationTable;
use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * @inheritdoc
 */
class ReservationCleanup implements ReservationCleanupInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName(CreateReservationTable::TABLE_NAME_RESERVATION);

        $select = $connection->select()
            ->from(
                $reservationTable,
                ['grouped_reservation_ids' => 'GROUP_CONCAT(' . ReservationInterface::RESERVATION_ID . ')']
            )
            ->group([ReservationInterface::STOCK_ID, ReservationInterface::SKU])
            ->having('SUM(' . ReservationInterface::QUANTITY . ') = 0');
        $groupedReservationIds = implode(',', $connection->fetchCol($select, 'grouped_reservation_ids'));

        $condition = [ReservationInterface::RESERVATION_ID . ' IN (?)' => $groupedReservationIds];
        $connection->delete($reservationTable, $condition);
    }
}
