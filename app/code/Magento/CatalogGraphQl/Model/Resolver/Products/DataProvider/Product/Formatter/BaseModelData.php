<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Grabs the data from the product
 */
class BaseModelData implements FormatterInterface
{
    /**
     * Get data from product
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = []) : array
    {
        return $product->getData();
    }
}
