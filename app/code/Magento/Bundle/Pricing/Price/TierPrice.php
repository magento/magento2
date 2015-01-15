<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Bundle tier prices model
 */
class TierPrice extends \Magento\Catalog\Pricing\Price\TierPrice implements DiscountProviderInterface
{
    /**
     * @var bool
     */
    protected $filterByBasePrice = false;

    /**
     * @var float|false
     */
    protected $percent;

    /**
     * Returns percent discount
     *
     * @return bool|float
     */
    public function getDiscountPercent()
    {
        if ($this->percent === null) {
            $percent = parent::getValue();
            $this->percent = ($percent) ? max(0, min(100, 100 - $percent)) : null;
        }
        return $this->percent;
    }

    /**
     * Returns pricing value
     *
     * @return bool|float
     */
    public function getValue()
    {
        if ($this->value !== null) {
            return $this->value;
        }

        $tierPrice = $this->getDiscountPercent();
        if ($tierPrice) {
            $regularPrice = $this->getRegularPrice();
            $this->value = $regularPrice * ($tierPrice / 100);
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
     * Returns true if first price is better
     *
     * Method filters tiers price values, higher discount value is better
     *
     * @param float $firstPrice
     * @param float $secondPrice
     * @return bool
     */
    protected function isFirstPriceBetter($firstPrice, $secondPrice)
    {
        return $firstPrice > $secondPrice;
    }

    /**
     * @return bool
     */
    public function isPercentageDiscount()
    {
        return true;
    }
}
