<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model\ResourceModel\SourceItemConfiguration;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryConfiguration\Model\GetSourceItemConfigurationInterface;
use Magento\InventoryConfiguration\Setup\Operation\CreateSourceConfigurationTable;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterfaceFactory;

/**
 * Class GetSourceItemConfiguration
 */
class GetSourceItemConfiguration implements GetSourceItemConfigurationInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * GetSourceItemConfiguration constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $sourceId, string $sku)
    {
        $connection = $this->resourceConnection->getConnection();

        $mainTable = $this->resourceConnection
            ->getTableName(CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION);

        $select = $connection->select()->from(
            ['mt' => $mainTable]
        )->where(
            'source_id=:source_id AND sku=:sku'
        );

        $bind = [
            'source_id' => $sourceId,
            'sku' => $sku
        ];

        $row = $connection->fetchRow($select, $bind);

        return $row ? $row : null;
    }
}
