<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleCatalogCache\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;

class PreventCachingPreloadedProductDataPlugin
{

    /**
     * Prevent caching preloader product data
     *
     * @param ProductRepositoryInterface $subject
     * @param string $sku
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return array
     */
    public function beforeGet($subject, $sku, $editMode = false, $storeId = null, $forceReload = false): array
    {
        return [$sku, $editMode, $storeId, true];
    }
}
