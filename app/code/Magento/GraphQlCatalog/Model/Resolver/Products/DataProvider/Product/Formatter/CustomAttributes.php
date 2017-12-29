<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\Formatter;

use Magento\Catalog\Model\Product;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Grabs the initial data from the product and fixes the id
 */
class CustomAttributes implements FormatterInterface
{
    /**
     * Fix entity id data
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = [])
    {
        foreach ($product->getCustomAttributes() as $customAttribute) {
            if (!isset($productData[$customAttribute->getAttributeCode()])) {
                $productData[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
            }
        }
        return $productData;
    }
}
