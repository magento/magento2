<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfiguration\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;
use Magento\InventoryConfiguration\Model\SourceItemConfigurationFactory;
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
     * @var SourceItemConfigurationFactory
     */
    private $sourceItemConfigurationFactory;


    /**
     * @param ResourceConnection $resourceConnection
     * @param SourceItemConfigurationFactory $sourceItemConfigurationFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SourceItemConfigurationFactory $sourceItemConfigurationFactory
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->sourceItemConfigurationFactory = $sourceItemConfigurationFactory;
    }

    /**
     * Get the source item configuration.
     *
     * @param $sourceId
     * @param $sku
     * @return SourceItemConfigurationInterface
     */
    public function execute($sourceId, $sku)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->columns(['source_id', SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY])->from(
            ['mt' => $this->getMainTable()]
        )->joinLeft(
            ['sit' => $this->getJoinTable()],
            'mt.source_item_id=sit.source_item_id'
        )->where(
            'source_id=:source_id AND sku=:sku'
        );

        $bind = [
            'source_id' => $sourceId,
            'sku' => $sku
        ];

        $row = $connection->fetchRow($select, $bind);
        $object = $this->sourceItemConfigurationFactory->create()->setData($row);

        return $object;
    }

    /**
     * Get the main table.
     *
     * @return string
     */
    protected function getMainTable()
    {
        return $this->resourceConnection->getTableName(SourceItemConfiguration::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);
    }

    /**
     * Get the join table.#
     *
     * @return string
     */
    protected function getJoinTable()
    {
        return $this->resourceConnection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);
    }



}