<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Check the product available discount and apply the correct discount to the price
 */
class DiscountCalculator
{

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(private readonly PriceCurrencyInterface $priceCurrency)
    {
    }

    /**
     * Apply percentage discount
     *
     * @param Product $product
     * @param float|null $value
     * @return float|null
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
        return (null !== $discount) ?
            $this->priceCurrency->roundPrice($discount/100 * $value, 2) : $value;
    }
}
