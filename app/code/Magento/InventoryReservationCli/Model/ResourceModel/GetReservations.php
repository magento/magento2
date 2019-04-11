<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Load a list of reservations by id.
 */
class GetReservations
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct (
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Load a list of reservations by id.
     *
     * @param array $reservationIds
     * @return array
     */
    public function execute(array $reservationIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_reservation');

        $qry = $connection
            ->select()
            ->from($tableName)
            ->where('reservation_id in (?)', $reservationIds);
        return $connection->fetchAll($qry);
    }
}
