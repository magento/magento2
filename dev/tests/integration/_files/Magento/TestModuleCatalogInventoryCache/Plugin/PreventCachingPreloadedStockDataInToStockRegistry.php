<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
