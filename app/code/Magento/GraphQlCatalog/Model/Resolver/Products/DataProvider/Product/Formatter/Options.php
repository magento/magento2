<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;

/**
 * Format a product's option information to conform to GraphQL schema representation
 */
class Options
{
    /**
     * Format product's option data to conform to GraphQL schema
     *
     * @param array $productData
     * @return array
     */
    public function format(array $productData)
    {
        if (isset($productData['options'])) {
            /** @var Option $option */
            foreach ($productData['options'] as $key => $option) {
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
