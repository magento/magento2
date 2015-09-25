<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Edit;

class WeightResolver
{
    /**
     * Product has weight
     */
    const HAS_WEIGHT = 1;

    /**
     * Product don't have weight
     */
    const HAS_NO_WEIGHT = 0;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function resolveProductHasWeight(\Magento\Catalog\Model\Product $product)
    {
        if (!$product->hasData('product_has_weight')) {
            throw new \LogicException(
                __('Product don\'t have "%1" key. It must be fulfilled during product edit', 'product_has_weight')
            );
        }
        return (bool) ($product->getData('product_has_weight') == self::HAS_WEIGHT);
    }
}
