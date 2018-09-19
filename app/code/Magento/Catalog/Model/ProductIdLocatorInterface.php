<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

/**
 * Product ID locator provides all product IDs by SKU.
 * @api
 * @since 101.1.0
 */
interface ProductIdLocatorInterface
{
    /**
     * Will return associative array of product ids as key and type as value grouped by SKUs.
     *
     * @param array $skus
     * @return array
     * @since 101.1.0
     */
    public function retrieveProductIdsBySkus(array $skus);
}
