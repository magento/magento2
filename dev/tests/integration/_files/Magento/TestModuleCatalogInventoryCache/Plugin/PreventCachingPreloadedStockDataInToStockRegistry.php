<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleCatalogInventoryCache\Plugin;

class PreventCachingPreloadedStockDataInToStockRegistry
{
    public function aroundSetStockItems(): void
    {
        //do not cache
    }

    public function aroundSetStockStatuses(): void
    {
        //do not cache
    }
}
