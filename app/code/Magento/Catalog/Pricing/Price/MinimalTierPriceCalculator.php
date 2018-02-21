<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * MinimalTierPriceCalculator shows minimal value of Tier Prices.
 */
class MinimalTierPriceCalculator implements MinimalPriceCalculatorInterface
{
    /**
     * Price Calculator interface.
     *
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * @param CalculatorInterface $calculator
     */
    public function __construct(CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Get raw value of "as low as" as a minimal among tier prices.
     *
     * @param SaleableInterface $saleableItem
     * @return float|null
     */
    public function getValue(SaleableInterface $saleableItem)
    {
        /** @var TierPrice $price */
        $price = $saleableItem->getPriceInfo()->getPrice(TierPrice::PRICE_CODE);
        $tierPriceList = $price->getTierPriceList();

        $tierPrices = [];
        foreach ($tierPriceList as $tierPrice) {
            /** @var AmountInterface $price */
            $price = $tierPrice['price'];
            $tierPrices[] = $price->getValue();
        }

        return $tierPrices ? min($tierPrices) : null;
    }

    /**
     * Return calculated amount object that keeps "as low as" value.
     *
     * @param SaleableInterface $saleableItem
     * @return AmountInterface|null
     */
    public function getAmount(SaleableInterface $saleableItem)
    {
        $value = $this->getValue($saleableItem);

        return $value === null ? null : $this->calculator->getAmount($value, $saleableItem);
    }
}
