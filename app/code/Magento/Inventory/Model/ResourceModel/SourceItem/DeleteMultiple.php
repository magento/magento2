<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\ResourceModel\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Implementation of SourceItem delete multiple operation for specific db layer
 * Delete Multiple used here for performance efficient purposes over single delete operation
 */
class DeleteMultiple
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
     * Multiple delete source items
     *
     * @param SourceItemInterface[] $sourceItems
     * @return void
     */
    public function execute(array $sourceItems)
    {
        if (!count($sourceItems)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);

        $sourceIds = $this->getSourceIds($sourceItems);
        foreach ($sourceIds as $sourceId => $skuList) {
            $whereCond = [
                $connection->quoteInto(SourceItemInterface::SOURCE_ID . ' = ?', $sourceId),
                $connection->quoteInto(SourceItemInterface::SKU . ' IN(?)', $skuList),
            ];
            $connection->delete($tableName, $whereCond);
        }
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return int[]
     */
    private function getSourceIds($sourceItems): array
    {
        $sourceIds = [];

        foreach ($sourceItems as $sourceItem) {
            $sourceIds[$sourceItem->getSourceId()][] = $sourceItem->getSku();
        }

        return $sourceIds;
    }
}
