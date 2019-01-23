<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\LocalCache;

/**
 * Local cache for service GetProductTypesBySkus.
 */
class GetProductTypesBySkusCache
{
    /**
     * @var array
     */
    private $productTypesBySkus = [];

    /**
     * Get data from cache.
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key):? array
    {
        return $this->productTypesBySkus[$key] ?? null;
    }

    /**
     * Add data to cache.
     *
     * @param string $key
     * @param array $productTypes
     */
    public function set(string $key, array $productTypes): void
    {
        $this->productTypesBySkus[$key] = $productTypes;
    }

    /**
     * Clean cache.
     *
     * @return void
     */
    public function clean(): void
    {
        $this->productTypesBySkus = [];
    }
}
