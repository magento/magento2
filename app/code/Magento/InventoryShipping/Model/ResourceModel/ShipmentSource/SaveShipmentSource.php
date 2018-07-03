<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\ResourceModel\ShipmentSource;

use Magento\Framework\App\ResourceConnection;

/**
 * Save Shipment Source
 */
class SaveShipmentSource
{
    /**
     * Constant for fields in data array
     */
    const SHIPMENT_ID = 'shipment_id';
    const SOURCE_CODE = 'source_code';
    
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
     * @param string $sourceCode
     * @return void
     */
    public function execute(int $shipmentId, string $sourceCode)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_shipment_source');

        $data = [
            self::SHIPMENT_ID => $shipmentId,
            self::SOURCE_CODE => $sourceCode
        ];

        $connection->insertOnDuplicate($tableName, $data, [self::SOURCE_CODE]);
    }
}
