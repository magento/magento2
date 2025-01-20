<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product;

/**
 * Product price interface for external catalogs
 *
 * @api
 * @since 100.0.2
 */
interface CatalogPriceInterface
{
    /**
     * Minimal price for "regular" user
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Store\Api\Data\StoreInterface $store Store view
     * @param bool $inclTax
     * @return null|float
     */
    public function getCatalogPrice(
        \Magento\Catalog\Model\Product $product,
        ?\Magento\Store\Api\Data\StoreInterface $store = null,
        $inclTax = false
    );

    /**
     * Calculate price without discount for external catalogs if applicable
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float|null
     */
    public function getCatalogRegularPrice(\Magento\Catalog\Model\Product $product);
}
