<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Format the new from and to typo of legacy fields news_from_date and news_to_date
 */
class NewFromTo implements FormatterInterface
{
    /**
     * Transfer data from legacy news_from_date and news_to_date to new names corespondent fields
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        if ($product->getData('news_from_date')) {
            $productData['new_from_date'] = $product->getData('news_from_date');
        }

        if ($product->getData('news_to_date')) {
            $productData['new_to_date'] = $product->getData('news_to_date');
        }

        return $productData;
    }
}
