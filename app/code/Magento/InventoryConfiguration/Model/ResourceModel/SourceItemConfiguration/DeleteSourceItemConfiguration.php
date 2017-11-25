<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfiguration\Setup\Operation\CreateSourceConfigurationTable;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Implementation of SourceItem Configuration delete operation for specific db layer
 */
class DeleteSourceItemConfiguration
{
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
     * Delete the source item configuration.
     *
     * @param int $sourceId
     * @param string $sku
     */
    public function execute(int $sourceId, string $sku)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection
            ->getTableName(CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);

        $connection->delete($tableName, [
            SourceItemConfigurationInterface::SOURCE_ID . ' = ?' => $sourceId,
            SourceItemConfigurationInterface::SKU . ' = ?' => $sku
        ]);
    }
}
