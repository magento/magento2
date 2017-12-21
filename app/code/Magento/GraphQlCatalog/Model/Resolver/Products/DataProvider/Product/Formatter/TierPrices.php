<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\TierPrice;

/**
 * Format a product's tier price information to conform to GraphQL schema representation
 */
class TierPrices
{
    /**
     * Format product's tier price data to conform to GraphQL schema
     *
     * @param Product $product
     * @param array $productData
     * @return array
     */
    public function format(Product $product, array $productData)
    {
        $tierPrices = $product->getTierPrices();
        if ($tierPrices) {
            /** @var TierPrice $tierPrice */
            foreach ($tierPrices as $tierPrice) {
                $productData['tier_prices'][] = $tierPrice->getData();
            }
        } else {
            $productData['tier_prices'] = null;
        }

        return $productData;
    }
}
