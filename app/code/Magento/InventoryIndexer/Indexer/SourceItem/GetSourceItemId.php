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
 * Get SourceItem id by product SKU and Source Code
 */
class GetSourceItemId
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
     * @param string $sku
     * @param string $sourceCode
     *
     * @return int
     */
    public function execute(string $sku, string $sourceCode): int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM),
                [SourceItemResourceModel::ID_FIELD_NAME]
            )
            ->where(SourceItemInterface::SKU . ' = ?', $sku)
            ->where(SourceItemInterface::SOURCE_CODE . ' = ?', $sourceCode);

        return (int)$connection->fetchOne($select);
    }
}
