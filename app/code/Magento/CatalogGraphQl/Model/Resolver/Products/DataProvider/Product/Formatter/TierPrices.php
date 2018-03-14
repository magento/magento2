<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\TierPrice;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Format a product's tier price information to conform to GraphQL schema representation
 */
class TierPrices implements FormatterInterface
{
    /**
     * Format product's tier price data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = []) : array
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
