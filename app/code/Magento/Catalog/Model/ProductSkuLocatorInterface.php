<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

/**
 * Product ID locator provides all product SKUs by ProductIds.
 * @api
 * @since 101.1.0
 */
interface ProductSkuLocatorInterface
{
    /**
     * Will return associative array of product skus as key and type as value grouped by ProductIds.
     *
     * @param array $productIds
     * @return array
     * @since 101.1.0
     */
    public function retrieveSkusByProductIds(array $productIds) : array;
}
