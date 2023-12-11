<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * Price is calculated depends on Price Code.
     * Existing logic was taken from methods \Magento\Catalog\Model\Product\Option\Value::(getPrice|getRegularPrice)
     * where $priceCode was hardcoded and changed to have dynamical approach.
     *
     * Examples of usage:
     *      \Magento\Catalog\Pricing\Price\CustomOptionPrice::getValue
     *      \Magento\Catalog\Model\Product\Option\Value::getPrice
     *      \Magento\Catalog\Model\Product\Option\Value::getRegularPrice
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
