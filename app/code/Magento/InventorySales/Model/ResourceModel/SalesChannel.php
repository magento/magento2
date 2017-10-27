<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Model\ResourceModel\PredefinedId;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Implementation of basic operations for Stock entity for specific db layer
 */
class SalesChannel extends AbstractDb
{
    /**
     * Provides possibility of saving entity with predefined/pre-generated id
     */
    use PredefinedId;

    /**#@+
     * Constants related to specific db layer
     */
    const TABLE_NAME_SALES_CHANNEL = 'inventory_stock_sales_channel';
    /**#@-*/

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME_SALES_CHANNEL, SalesChannelInterface::ID);
    }
}
