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
 * Populates the custom attributes
 */
class CustomAttributes implements FormatterInterface
{
    /**
     * Populate the defined custom attributes
     *
     * {@inheritdoc}
     */
    public function format(Product $product, array $productData = []) : array
    {
        foreach ($product->getCustomAttributes() as $customAttribute) {
            if (!isset($productData[$customAttribute->getAttributeCode()])) {
                $productData[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
            }
        }
        return $productData;
    }
}
