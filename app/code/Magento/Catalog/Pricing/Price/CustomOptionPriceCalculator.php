<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Model\Product\Option\Value as ProductOptionValue;

/**
 *  Calculates prices of custom options of the product.
 */
class CustomOptionPriceCalculator
{
    /**
     * @param ProductOptionValue $optionValue
     * @param string $priceCode
     * @return float|int
     */
    public function getOptionPriceByPriceCode(
        ProductOptionValue $optionValue,
        string $priceCode = \Magento\Catalog\Pricing\Price\BasePrice::PRICE_CODE
    ) {
        if ($optionValue->getPriceType() === ProductOptionValue::TYPE_PERCENT) {
            $basePrice = $optionValue->getOption()->getProduct()->getPriceInfo()->getPrice($priceCode)->getValue();
            $price = $basePrice * ($optionValue->getData(ProductOptionValue::KEY_PRICE) / 100);
            return $price;
        }
        return $optionValue->getData(ProductOptionValue::KEY_PRICE);
    }
}
