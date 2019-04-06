<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservations\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

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
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_reservation');

        $qry = $connection
            ->select()
            ->from($tableName,
                [
                    'OrderId' => new \Zend_Db_Expr("CAST(JSON_EXTRACT(metadata, '$.object_id') as UNSIGNED)"),
                    'ReservationTot' => 'sum(quantity)'
                ]
            )
            ->group('OrderId')
            ->having('ReservationTot != ?', 0);
        return $connection->fetchAll($qry);
    }
}
