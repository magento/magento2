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
     * @var int
     */
    private $groupConcatMaxLen;

    /**
     * @param ResourceConnection $resource
     * @param int $groupConcatMaxLen
     */
    public function __construct(
        ResourceConnection $resource,
        int $groupConcatMaxLen
    ) {
        $this->resource = $resource;
        $this->groupConcatMaxLen = $groupConcatMaxLen;
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
                ['GROUP_CONCAT(' . ReservationInterface::RESERVATION_ID . ')']
            )
            ->group([ReservationInterface::STOCK_ID, ReservationInterface::SKU])
            ->having('SUM(' . ReservationInterface::QUANTITY . ') = 0');
            $connection->query('SET group_concat_max_len = ' . $this->groupConcatMaxLen);
        $groupedReservationIds = implode(',', $connection->fetchCol($select));

        $condition = [ReservationInterface::RESERVATION_ID . ' IN (?)' => explode(',', $groupedReservationIds)];
        $connection->delete($reservationTable, $condition);
    }
}
