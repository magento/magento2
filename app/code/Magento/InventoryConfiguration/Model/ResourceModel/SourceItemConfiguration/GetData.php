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
 * Get configuration data for specific source item
 */
class GetData
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
     * @param int $sourceId
     * @param string $sku
     * @return array|null
     */
    public function execute(int $sourceId, string $sku)
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceItemConfigurationTable = $this->resourceConnection
            ->getTableName(CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);

        $select = $connection->select()
            ->from($sourceItemConfigurationTable)
            ->where(SourceItemConfigurationInterface::SOURCE_ID . ' = ?', $sourceId)
            ->where(SourceItemConfigurationInterface::SKU . ' = ?', $sku);

        $row = $connection->fetchRow($select);
        return $row ? $row : null;
    }
}
