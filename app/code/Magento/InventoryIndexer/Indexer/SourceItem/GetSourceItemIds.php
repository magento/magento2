<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Get SourceItem ids from array of SourceItems
 */
class GetSourceItemIds
{
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
     * @param SourceItemInterface[] $sourceItems
     * @return array
     */
    public function execute(array $sourceItems): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM),
                [SourceItemResourceModel::ID_FIELD_NAME]
            );
        foreach ($sourceItems as $sourceItem) {
            $sku = $connection->quote($sourceItem->getSku());
            $sourceCode = $connection->quote($sourceItem->getSourceCode());
            $select->orWhere(
                SourceItemInterface::SKU . " = {$sku} AND " .
                SourceItemInterface::SOURCE_CODE ." = {$sourceCode}"
            );
        }

        return $connection->fetchCol($select, SourceItemResourceModel::ID_FIELD_NAME);
    }
}
