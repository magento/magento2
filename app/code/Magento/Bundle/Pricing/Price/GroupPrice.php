<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Bundle group price model
 */
class GroupPrice extends \Magento\Catalog\Pricing\Price\GroupPrice implements DiscountProviderInterface
{
    /**
     * @var float|false
     */
    protected $percent;

    /**
     * Returns percent discount value
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
     * Returns pice value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value !== null) {
            return $this->value;
        }

        $groupPrice = $this->getDiscountPercent();
        if ($groupPrice) {
            $regularPrice = $this->getRegularPrice();
            $this->value = $regularPrice * ($groupPrice / 100);
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
