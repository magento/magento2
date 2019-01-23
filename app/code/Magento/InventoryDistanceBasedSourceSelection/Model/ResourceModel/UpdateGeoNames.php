<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Update geonames for a specific country
 */
class UpdateGeoNames
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * UpdateGeoNames constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Update country geo names
     *
     * @param array $geoNames
     * @param string $countryCode
     */
    public function execute(array $geoNames, string $countryCode): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_geoname');

        $connection->beginTransaction();
        $connection->delete($tableName, $connection->quoteInto('country_code = ?', $countryCode));
        $connection->insertMultiple($tableName, $geoNames);
        $connection->commit();
    }
}
