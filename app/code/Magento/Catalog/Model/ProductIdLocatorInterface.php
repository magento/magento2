<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model;

/**
 * Product ID locator provides all product IDs by SKU.
 * @api
 * @since 102.0.0
 */
interface ProductIdLocatorInterface
{
    /**
     * Will return associative array of product ids as key and type as value grouped by SKUs.
     *
     * @param array $skus
     * @return array
     * @since 102.0.0
     */
    public function retrieveProductIdsBySkus(array $skus);
}
