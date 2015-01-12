<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Special price model
 */
class SpecialPrice extends AbstractPrice implements SpecialPriceInterface, BasePriceProviderInterface
{
    /**
     * Price type special
     */
    const PRICE_CODE = 'special_price';

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->localeDate = $localeDate;
    }

    /**
     * @return bool|float
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = false;
            $specialPrice = $this->getSpecialPrice();
            if (!is_null($specialPrice) && $specialPrice !== false && $this->isScopeDateInInterval()) {
                $this->value = (float) $specialPrice;
            }
        }

        return $this->value;
    }

    /**
     * Returns special price
     *
     * @return float
     */
    public function getSpecialPrice()
    {
        $specialPrice = $this->product->getSpecialPrice();
        if (!is_null($specialPrice) && $specialPrice !== false && !$this->isPercentageDiscount()) {
            $specialPrice = $this->priceCurrency->convertAndRound($specialPrice);
        }
        return $specialPrice;
    }

    /**
     * Returns starting date of the special price
     *
     * @return mixed
     */
    public function getSpecialFromDate()
    {
        return $this->product->getSpecialFromDate();
    }

    /**
     * Returns end date of the special price
     *
     * @return mixed
     */
    public function getSpecialToDate()
    {
        return $this->product->getSpecialToDate();
    }

    /**
     * @return bool
     */
    public function isScopeDateInInterval()
    {
        return $this->localeDate->isScopeDateInInterval(
            $this->product->getStore(),
            $this->getSpecialFromDate(),
            $this->getSpecialToDate()
        );
    }

    /**
     * @return bool
     */
    public function isPercentageDiscount()
    {
        return false;
    }
}
