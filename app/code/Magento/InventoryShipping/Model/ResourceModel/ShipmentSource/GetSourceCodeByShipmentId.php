<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\ResourceModel\ShipmentSource;

use Magento\Framework\App\ResourceConnection;

/**
 * Get source code by shipment Id
 */
class GetSourceCodeByShipmentId
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
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get the source code by shipment Id
     *
     * @param int $shipmentId
     * @return string|null
     */
    public function execute(int $shipmentId)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection
            ->getTableName('inventory_shipment_source');

        $select = $connection->select()
            ->from($tableName, [
                self::SOURCE_CODE => self::SOURCE_CODE
            ])
            ->where(self::SHIPMENT_ID . ' = ?', $shipmentId)
            ->limit(1);

        $sourceCode = $connection->fetchOne($select);

        return $sourceCode ?: null;
    }
}
