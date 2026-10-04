<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Cart layer product loader.
 */
interface ProductReaderInterface
{
    /**
     * Load products by skus for specified store.
     *
     * @param string[] $skus
     * @param int $storeId
     * @return void
     */
    public function loadProducts(array $skus, int $storeId);

    /**
     * Get product by specified sku.
     *
     * @param string $sku
     * @return ProductInterface
     */
    public function getProductBySku(string $sku) : ?ProductInterface;
}
