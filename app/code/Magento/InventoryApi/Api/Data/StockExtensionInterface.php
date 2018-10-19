<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

/**
 * TODO: temporal fix of extension classes generation during installation
 * ExtensionInterface class for @see \Magento\InventoryApi\Api\Data\StockInterface
 */
interface StockExtensionInterface extends \Magento\Framework\Api\ExtensionAttributesInterface
{
    /**
     * @return \Magento\InventorySalesApi\Api\Data\SalesChannelInterface[]|null
     */
    public function getSalesChannels(): ?array;

    /**
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface[]|null $salesChannels
     * @return void
     */
    public function setSalesChannels(?array $salesChannels): void;
}
