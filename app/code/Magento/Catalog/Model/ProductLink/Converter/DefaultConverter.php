<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink\Converter;

/**
 * Class \Magento\Catalog\Model\ProductLink\Converter\DefaultConverter
 *
 * @since 2.0.0
 */
class DefaultConverter implements ConverterInterface
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
            'position' => $product->getPosition()
        ];
    }
}
