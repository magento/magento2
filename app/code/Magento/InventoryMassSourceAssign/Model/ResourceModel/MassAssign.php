<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssign\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Do not use this class directly
 * @see \Magento\InventoryMassSourceAssignApi\Api\MassAssignInterface
 */
class MassAssign
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Assign sources to products
     * @param SourceItemInterface[] $sourceItems
     * @return int
     */
    public function execute(array $sourceItems): int
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        $count = 0;
        foreach ($sourceItems as $sourceItem) {
            /** @var SourceItemInterface $sourceItem */
            try {
                $connection->insert($tableName, [
                    SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                    SourceItemInterface::SKU => $sourceItem->getSku(),
                    SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                    SourceItemInterface::STATUS => $sourceItem->getStatus(),
                ]);
            } catch (DuplicateException $e) {
                // Skip if source assignment is duplicated
                continue;
            }

            $count++;
        }

        return $count;
    }
}
