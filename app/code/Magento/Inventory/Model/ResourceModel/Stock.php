<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Inventory\Setup\InstallSchema;
use Magento\InventoryApi\Api\Data\StockInterface;

class Stock extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME_STOCK, StockInterface::STOCK_ID);
    }
}
