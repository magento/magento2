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
 * Fixed the id related data in the product data
 */
class EntityIdToId implements FormatterInterface
{
    /**
     * Fix entity id data by converting it to an id key
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = []) : array
    {
        $productData['id'] = $product->getId();
        unset($productData['entity_id']);

        return $productData;
    }
}
