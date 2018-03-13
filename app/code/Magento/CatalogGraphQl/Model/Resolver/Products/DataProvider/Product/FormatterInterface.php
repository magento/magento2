<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Model\Product;

/**
 * Transforms data of a product to its GraphQL type format"
 */
interface FormatterInterface
{
    /**
     * Format single product data to GraphQl type structure
     *
     * @param Product $product
     * @param array $productData
     * @return array
     */
    public function format(Product $product, array $productData = []);
}
