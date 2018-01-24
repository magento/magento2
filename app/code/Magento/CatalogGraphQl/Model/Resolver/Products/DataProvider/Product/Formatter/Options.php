<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Format a product's option information to conform to GraphQL schema representation
 */
class Options implements FormatterInterface
{
    /**
     * Format product's option data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if (!empty($product->getOptions())) {
            /** @var Option $option */
            foreach ($product->getOptions() as $key => $option) {
                $productData['options'][$key] = $option->getData();
                $productData['options'][$key]['product_sku'] = $option->getProductSku();
                $values = $option->getValues() ?: [];
                /** @var Option\Value $value */
                foreach ($values as $value) {
                    $productData['options'][$key]['values'][] = $value->getData();
                }
            }
        }

        return $productData;
    }
}
