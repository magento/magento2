<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Retrieve source items for a defined set of skus and sorted source codes
 */
class GetAvailableSourceItemsDataBySkusAndSortedSource
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
     * @param array $skus
     * @param array $sourceCodes
     * @return array[]
     */
    public function execute(array $skus, array $sourceCodes): array
    {
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);
        $connection = $this->resourceConnection->getConnection();

        $qry = $connection
            ->select()
            ->from($tableName)
            ->where(SourceItemInterface::SKU . ' IN (?)', $skus)
            ->where(SourceItemInterface::SOURCE_CODE . ' IN (?)', $sourceCodes)
            ->where(SourceItemInterface::QUANTITY . ' > 0')
            ->where(SourceItemInterface::STATUS . ' = ?', SourceItemInterface::STATUS_IN_STOCK)
            ->order($connection->quoteInto('FIELD(' . SourceItemInterface::SOURCE_CODE . ', ?)', $sourceCodes));

        return $connection->fetchAll($qry) ?? [];
    }
}
