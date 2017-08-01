<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\Product\Link\ProductEntity;

use Magento\Catalog\Model\ProductLink\Converter\ConverterInterface;

/**
 * Class \Magento\GroupedProduct\Model\Product\Link\ProductEntity\Converter
 *
 * @since 2.0.0
 */
class Converter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convert(\Magento\Catalog\Model\Product $product)
    {
        return [
            'type' => $product->getTypeId(),
            'sku' => $product->getSku(),
            'position' => $product->getPosition(),
            'custom_attributes' => [
                ['attribute_code' => 'qty', 'value' => $product->getQty()],
            ]
        ];
    }
}
