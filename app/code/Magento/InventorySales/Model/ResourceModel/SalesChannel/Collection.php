<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\InventorySales\Model\ResourceModel\SalesChannel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\InventorySales\Model\ResourceModel\SalesChannel as SalesChannelResourceModel;
use Magento\InventorySales\Model\SalesChannel as SalesChannelModel;

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
        $this->_init(SalesChannelModel::class, SalesChannelResourceModel::class);
    }


    public function addFilterByStockId(int $stockId)
    {
        $this->addFilter('stock_id', $stockId);
        return $this;
    }

    public function addFilterByTypeAndCode(int $type, string $code)
    {
        $this->addFilter('type', $type);
        $this->addFilter('code', $code);
        return $this;
    }
}
