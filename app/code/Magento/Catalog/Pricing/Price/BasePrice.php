<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Class BasePrice
 * @since 2.0.0
 */
class BasePrice extends AbstractPrice
{
    /**
     * Price type identifier string
     */
    const PRICE_CODE = 'base_price';

    /**
     * Get Base Price Value
     *
     * @return float|bool
     * @since 2.0.0
     */
    public function getValue()
    {
        if ($this->value === null) {
            $this->value = false;
            foreach ($this->priceInfo->getPrices() as $price) {
                if ($price instanceof BasePriceProviderInterface && $price->getValue() !== false) {
                    $this->value = min($price->getValue(), $this->value ?: $price->getValue());
                }
            }
        }
        return $this->value;
    }
}
