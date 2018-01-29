<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProductGraphQl\Model\Plugin\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProduct;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Post formatting plugin to continue formatting data for grouped type products
 */
class GroupedProductLinks implements FormatterInterface
{

    /**
     * Add grouped options and options to configurable types
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if ($product->getTypeId() === GroupedProduct::TYPE_CODE) {
            $productData['product_links'] = array_merge(
                isset($productData['product_links']) ? $productData['product_links'] : [],
                $product->getProductLinks()
            );
        }

        return $productData;
    }
}
