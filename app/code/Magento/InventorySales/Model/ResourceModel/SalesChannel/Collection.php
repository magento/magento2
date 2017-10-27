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
 * Resource collection of sales channel items entity
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

    /**
     * Returns the linked sales channels for the given stock id.
     *
     * @param int $stockId
     * @return Collection
     */
    public function addFilterByStockId(int $stockId): Collection
    {
        $this->addFilter('stock_id', $stockId);
        return $this;
    }

    /**
     * Returns sales channel that matches the given type and code.
     *
     * @param string $type
     * @param string $code
     * @return Collection
     */
    public function addFilterByTypeAndCode(string $type, string $code): Collection
    {
        $this->addFilter('type', $type);
        $this->addFilter('code', $code);
        return $this;
    }
}
