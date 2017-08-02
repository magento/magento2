<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;

/**
 * Class DiscountCalculator
 * @since 2.0.0
 */
class DiscountCalculator
{
    /**
     * Apply percentage discount
     *
     * @param Product $product
     * @param float|null $value
     * @return float|null
     * @since 2.0.0
     */
    public function calculateDiscount(Product $product, $value = null)
    {
        if ($value === null) {
            $value = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
        }

        $discount = null;
        foreach ($product->getPriceInfo()->getPrices() as $price) {
            if ($price instanceof DiscountProviderInterface && $price->getDiscountPercent()) {
                $discount = min($price->getDiscountPercent(), $discount ?: $price->getDiscountPercent());
            }
        }
        return (null !== $discount) ?  $discount/100 * $value : $value;
    }
}
