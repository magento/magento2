<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

/**
 * Product ID locator provides all product IDs by SKU.
 */
interface ProductIdLocatorInterface
{
    /**
     * Will return associative array of product ids as key and type as value grouped by SKUs.
     *
     * @param array $skus
     * @return array
     */
    public function retrieveProductIdsBySkus(array $skus);
}
