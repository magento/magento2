<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\Reservation;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Setup\Operation\CreateReservationTable;
use Magento\InventoryApi\Api\Data\ReservationInterface;

/**
 * The resource model responsible for retrieving Reservation Quantity.
 * Used by Service Contracts that are agnostic to the Data Access Layer.
 */
class ReservationQuantity
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
     * Given a product sku and a stock id, return reservation quantity.
     *
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    public function execute(string $sku, int $stockId): float
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName(CreateReservationTable::TABLE_NAME_RESERVATION);

        $select = $connection->select()
            ->from($reservationTable, [ReservationInterface::QUANTITY => 'SUM(' . ReservationInterface::QUANTITY . ')'])
            ->where(ReservationInterface::SKU . ' = ?', $sku)
            ->where(ReservationInterface::STOCK_ID . ' = ?', $stockId)
            ->limit(1);

        $reservationQty = $connection->fetchOne($select);
        if (false === $reservationQty) {
            $reservationQty = 0;
        }
        return (float)$reservationQty;
    }
}
