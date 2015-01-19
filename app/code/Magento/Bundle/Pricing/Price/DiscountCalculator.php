<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;

/**
 * Class DiscountCalculator
 */
class DiscountCalculator
{
    /**
     * Apply percentage discount
     *
     * @param Product $product
     * @param float|null $value
     * @return float|null
     */
    public function calculateDiscount(Product $product, $value = null)
    {
        if (is_null($value)) {
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
