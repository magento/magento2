<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Regular Price model
 */
class RegularPrice extends AbstractPrice implements BasePriceProviderInterface
{
    /**
     * Price type
     */
    public const PRICE_CODE = 'regular_price';

    /**
     * Get price value
     *
     * @return float
     */
    public function getValue()
    {
        if ($this->value === null) {
            $price = $this->product->getPrice();
            $priceInCurrentCurrency = $this->priceCurrency->convert($price);
            $this->value = $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : 0;
        }
        return $this->value;
    }
}
