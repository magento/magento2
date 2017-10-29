<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterfaceFactory;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;

/**
 * Implementation of SourceItem Quantity notification save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class GetSourceItemConfiguration
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SourceItemConfigurationInterfaceFactory
     */
    private $sourceItemConfigurationFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SourceItemConfigurationInterfaceFactory $sourceItemConfigurationFactory
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
    }

    /**
     * Get the source item configuration.
     *
     * @param string $sourceId
     * @param string $sku
     * @return SourceItemConfigurationInterface
     */
    public function execute(string $sourceId, string $sku)
    {
        $connection = $this->resourceConnection->getConnection();

        $mainTable = $this->resourceConnection->getTableName(SourceItemConfiguration::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);
        $joinTable = $this->resourceConnection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);

        $select = $connection->select()->from(
            ['mt' => $mainTable],
            [SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY]
        )->joinRight(
            ['sit' => $joinTable],
            'mt.source_item_id=sit.source_item_id',
            ['source_item_id', 'source_id']
        )->where(
            'source_id=:source_id AND sku=:sku'
        );

        $bind = [
            'source_id' => $sourceId,
            'sku' => $sku
        ];

        $row = $connection->fetchRow($select, $bind);
        $object = $this->sourceItemConfigurationFactory->create();

        if ($row && count($row) > 0) {
            foreach ($row as $key => $column) {
                if ($column === null) {
                    unset($row[$key]);
                }
            }
            $object = $this->sourceItemConfigurationFactory->create()->setData($row);
        }

        return $object;
    }
}