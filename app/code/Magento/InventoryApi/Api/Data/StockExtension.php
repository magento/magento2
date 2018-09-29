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
     * @inheritdoc
     */
    public function getSalesChannels(): ?array
    {
        return $this->_get('sales_channels');
    }

    /**
     * @inheritdoc
     */
    public function setSalesChannels(?array $salesChannels): void
    {
        $this->setData('sales_channels', $salesChannels);
    }
}
