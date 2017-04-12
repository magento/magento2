<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Special price model
 */
class SpecialPrice extends \Magento\Catalog\Pricing\Price\SpecialPrice implements DiscountProviderInterface
{
    /**
     * @var float|false
     */
    protected $percent;

    /**
     * Returns discount percent
     *
     * @return bool|float
     */
    public function getDiscountPercent()
    {
        if ($this->percent === null) {
            $this->percent = parent::getValue();
        }
        return $this->percent;
    }

    /**
     * Returns price value
     *
     * @return bool|float
     */
    public function getValue()
    {
        if ($this->value !== null) {
            return $this->value;
        }

        $specialPrice = $this->getDiscountPercent();
        if ($specialPrice) {
            $regularPrice = $this->getRegularPrice();
            $this->value = $regularPrice * ($specialPrice / 100);
        } else {
            $this->value = false;
        }
        return $this->value;
    }

    /**
     * Returns regular price
     *
     * @return bool|float
     */
    protected function getRegularPrice()
    {
        return $this->priceInfo->getPrice(RegularPrice::PRICE_CODE)->getValue();
    }

    /**
     * @return bool
     */
    public function isPercentageDiscount()
    {
        return true;
    }
}
