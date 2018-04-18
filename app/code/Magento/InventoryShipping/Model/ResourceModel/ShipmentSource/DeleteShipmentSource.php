<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\ResourceModel\ShipmentSource;

use Magento\Framework\App\ResourceConnection;

/**
 * Delete Shipment Source
 */
class DeleteShipmentSource
{
    /**
     * Constant for fields in data array
     */
    const SHIPMENT_ID = 'shipment_id';
    
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int $shipmentId
     * @return void
     */
    public function execute(int $shipmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_shipment_source');

        $connection->delete($tableName, [
            self::SHIPMENT_ID . ' = ?' => $shipmentId,
        ]);
    }
}
