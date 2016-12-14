<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfoInterface;

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
            $prices = $this->getStoredTierPrices();
            $prevQty = PriceInfoInterface::PRODUCT_QUANTITY_DEFAULT;
            $this->value = $prevPrice = false;
            $priceGroup = $this->groupManagement->getAllCustomersGroup()->getId();

            foreach ($prices as $price) {
                if (!$this->canApplyTierPrice($price, $priceGroup, $prevQty)
                    || !isset($price['percentage_value'])
                    || !is_numeric($price['percentage_value'])
                ) {
                    continue;
                }
                if (false === $prevPrice || $this->isFirstPriceBetter($price['website_price'], $prevPrice)) {
                    $prevPrice = $price['website_price'];
                    $prevQty = $price['price_qty'];
                    $priceGroup = $price['cust_group'];
                    $this->percent = max(0, min(100, 100 - $price['percentage_value']));
                }
            }
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
