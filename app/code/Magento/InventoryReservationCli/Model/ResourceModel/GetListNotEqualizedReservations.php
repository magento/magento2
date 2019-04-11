<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Load a list of uncompensated reservations.
 */
class GetListNotEqualizedReservations
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
     * Load a list of uncompensated reservations.
     *
     * @return array
     */
    public function execute(): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_reservation');

        $qry = $connection
            ->select()
            ->from(['r' => $tableName], ['reservations' => 'GROUP_CONCAT(r.reservation_id)'])
            ->group(['r.stock_id', 'r.sku'])
            ->having('SUM(r.quantity) != 0');
        return $connection->fetchRow($qry);
    }
}
