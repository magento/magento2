<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Inventory\Model\ResourceModel\SourceItem\MultipleSave;
use Magento\Inventory\Setup\InstallSchema;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Class SourceItem
 */
class SourceItem extends AbstractDb
{
    /**
     * @var MultipleSave
     */
    private $multipleSave;

    /**
     * @param Context $context
     * @param MultipleSave $multipleSave
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MultipleSave $multipleSave,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->multipleSave = $multipleSave;
    }


    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME_SOURCE_ITEM, SourceItemInterface::SOURCE_ITEM_ID);
    }

    /**
     * Multiple save source items
     *
     * @param SourceItemInterface[] $sourceItems
     * @return void
     */
    public function multipleSave(array $sourceItems)
    {
        $this->multipleSave->multipleSave($sourceItems);
    }
}
