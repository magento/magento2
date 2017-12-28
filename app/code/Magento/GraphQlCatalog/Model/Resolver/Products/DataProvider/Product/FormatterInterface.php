<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Use this as a post processor class after you grabbed the data from a product
 */
interface FormatterInterface
{
    /**
     * Format/Modify single product data from object to an array
     *
     * @param ProductInterface $product
     * @param array $productData
     * @return array
     */
    public function format(ProductInterface $product, array $productData = []);
}
