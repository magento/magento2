<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product\Option\Value as ProductOptionValue;

/**
 *  Calculates prices of custom options of the product.
 */
class CustomOptionPriceCalculator
{
    /**
     * Calculates prices of custom option by code.
     *
     * @param ProductOptionValue $optionValue
     * @param string $priceCode
     * @return float|int
     */
    public function getOptionPriceByPriceCode(
        ProductOptionValue $optionValue,
        string $priceCode = BasePrice::PRICE_CODE
    ) {
        if ($optionValue->getPriceType() === ProductOptionValue::TYPE_PERCENT) {
            $basePrice = $optionValue->getOption()->getProduct()->getPriceInfo()->getPrice($priceCode)->getValue();
            $price = $basePrice * ($optionValue->getData(ProductOptionValue::KEY_PRICE) / 100);
            return $price;
        }
        return $optionValue->getData(ProductOptionValue::KEY_PRICE);
    }
}
