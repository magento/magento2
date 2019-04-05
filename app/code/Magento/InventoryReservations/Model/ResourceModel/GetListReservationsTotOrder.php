<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model\ResourceModel;


use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;

class GetListReservationsTotOrder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * GetListReservationsTotOrder constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct (
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(): array
    {
        $tableName = $this->resourceConnection->getTableName('inventory_reservation');
        $tableSalesOrderName = $this->resourceConnection->getTableName('sales_order');
        $connection = $this->resourceConnection->getConnection();

        //todo: modularity rework
        $qry = $connection
            ->select()
            ->from($tableName, ['ReservationTot' => 'sum(quantity)'])
            ->joinInner($tableSalesOrderName,
                'entity_id = ' . new \Zend_Db_Expr("CAST(JSON_EXTRACT(metadata, '$.object_id') as UNSIGNED)
    AND state IN ('complete', 'closed', 'canceled')"), ['IncrementId' => 'increment_id']
            )
            ->group('IncrementId')
            ->having('ReservationTot != ?', 0);
        return $connection->fetchAll($qry);
    }
}
