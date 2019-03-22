<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Update source items data in one single database operation
 */
class UpdateSourceItemsData
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
     * @param array $sourceItemsData
     */
    public function execute(array $sourceItemsData): void
    {
        if (empty($sourceItemsData)) {
            return;
        }

        $tableName = $this->resourceConnection->getTableName('inventory_source_item');

        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(
            $tableName,
            $sourceItemsData,
            [
                SourceItemInterface::QUANTITY,
                SourceItemInterface::STATUS,
            ]
        );
    }
}
