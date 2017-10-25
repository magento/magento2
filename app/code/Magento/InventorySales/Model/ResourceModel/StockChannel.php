<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Model\ResourceModel\PredefinedId;
use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Implementation of basic operations for Stock entity for specific db layer
 */
class StockChannel extends AbstractDb
{
    /**
     * Provides possibility of saving entity with predefined/pre-generated id
     */
    use PredefinedId;

    /**#@+
     * Constants related to specific db layer
     */
    const TABLE_NAME_STOCK_CHANNEL = 'inventory_stock_channel';
    /**#@-*/

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_STOCK_CHANNEL, StockInterface::STOCK_CHANNEL_ID);
    }
}
