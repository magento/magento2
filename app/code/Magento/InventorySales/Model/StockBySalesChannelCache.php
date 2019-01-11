<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventoryApi\Api\Data\StockInterface;

class StockBySalesChannelCache
{
    /**
     * @var StockInterface[]
     */
    private $stockBySalesChannel = [];

    /**
     * Get stock from cache by sales channel code and type.
     *
     * @param string $salesChannelCode
     * @param string $salesChannelType
     * @return StockInterface|null
     */
    public function get(string $salesChannelCode, string $salesChannelType): ?StockInterface
    {
        $cacheKey = $salesChannelCode . '_'. $salesChannelType;

        return $this->stockBySalesChannel[$cacheKey] ?? null;
    }

    /**
     * Set stock to cache by sales channel code and type.
     *
     * @param string $salesChannelCode
     * @param string $salesChannelType
     * @param StockInterface $stock
     * @return void
     */
    public function set(string $salesChannelCode, string $salesChannelType, StockInterface $stock): void
    {
        $cacheKey = $salesChannelCode . '_'. $salesChannelType;
        $this->stockBySalesChannel[$cacheKey] = $stock;
    }

    /**
     * Clean cache.
     *
     * @return void
     */
    public function clean(): void
    {
        $this->stockBySalesChannel = [];
    }
}
