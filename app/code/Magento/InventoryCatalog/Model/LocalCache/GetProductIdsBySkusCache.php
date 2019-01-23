<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LocalCache;

/**
 * Local cache for service GetProductIdsBySkus.
 */
class GetProductIdsBySkusCache
{
    /**
     * @var array
     */
    private $productIdsBySkus = [];

    /**
     * Get data from cache.
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key):? array
    {
        return $this->productIdsBySkus[$key] ?? null;
    }

    /**
     * Add data to cache.
     *
     * @param string $key
     * @param array $data
     */
    public function set(string $key, array $data): void
    {
        $this->productIdsBySkus[$key] = $data;
    }

    /**
     * Clean cache.
     *
     * @return void
     */
    public function clean(): void
    {
        $this->productIdsBySkus = [];
    }
}
