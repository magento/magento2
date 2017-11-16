<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

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
     * @param int $sourceItemId
     * @internal param string $sku
     */
    public function execute(int $sourceItemId)
    {
        $connection = $this->resourceConnection->getConnection();
        $mainTable = $this->resourceConnection
            ->getTableName(SourceItemConfiguration::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);
        $condition = ['source_item_id = ?' => $sourceItemId];

        $connection->delete($mainTable, $condition);
    }
}
