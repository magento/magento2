<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\GroupedProduct\Model\Product\Link\ProductEntity;

use Magento\Catalog\Model\ProductLink\Converter\ConverterInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Converter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(\Magento\Catalog\Model\Product $product)
    {
        return [
            'type' => $product->getTypeId(),
            'sku' => $product->getSku(),
            'position' => $product->getPosition(),
            'custom_attributes' => [
                ['attribute_code' => 'qty', 'value' => $product->getData(Grouped::GROUPED_LINK_QTY) ?? $product->getQty()],
            ]
        ];
    }
}
