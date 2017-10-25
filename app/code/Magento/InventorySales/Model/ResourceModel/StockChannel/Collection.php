<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySales\Model\ResourceModel\StockChannel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\InventorySales\Model\ResourceModel\StockChannel as StockChannelResourceModel;
use Magento\InventorySales\Model\StockChannel as StockChannelModel;

/**
 * Resource Collection of Source Items entity
 *
 * @api
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(StockChannelModel::class, StockChannelResourceModel::class);
    }
}
