<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Setup\InstallSchema;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Class SourceItem
 */
class SourceItem extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME_SOURCE_ITEM, SourceItemInterface::SOURCE_ITEM_ID);
    }

    /**
     * Multiple Save Source item data
     *
     * @param array $sourceItemsData
     * @return void
     */
    public function multipleSave(array $sourceItemsData)
    {
        $connection = $this->getConnection();
        $connection->insertMultiple($connection->getTableName(InstallSchema::TABLE_NAME_SOURCE_ITEM), $sourceItemsData);
    }
}
