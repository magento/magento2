<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

/**
 * TODO: temporal fix of extension classes generation during installation
 * Extension class for @see \Magento\InventoryApi\Api\Data\StockInterface
 */
class StockExtension extends \Magento\Framework\Api\AbstractSimpleObject implements StockExtensionInterface
{
    /**
     * @return \Magento\InventorySalesApi\Api\Data\SalesChannelInterface[]|null
     */
    public function getSalesChannels()
    {
        return $this->_get('sales_channels');
    }

    /**
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface[] $salesChannels
     * @return $this
     */
    public function setSalesChannels($salesChannels)
    {
        $this->setData('sales_channels', $salesChannels);
        return $this;
    }
}
