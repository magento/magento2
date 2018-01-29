<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\TierPrice;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Format the product links information to conform to GraphQL schema representation
 */
class ProductLinks implements FormatterInterface
{
    /**
     * Format product links data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        $productLinks = $product->getProductLinks();
        if ($productLinks) {
            /** @var TierPrice $tierPrice */
            foreach ($productLinks as $productLink) {
                $productData['product_links'][] = $productLink->getData();
            }
        } else {
            $productData['product_links'] = null;
        }

        return $productData;
    }
}
