<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Price;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * As Low As shows minimal value of Tier Prices
 */
class MinimalTierPriceCalculator implements MinimalPriceCalculatorInterface
{
    /**
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * @var AmountInterface|null
     */
    private $lowestTierPrice = null;

    /**
     * @param CalculatorInterface $calculator
     */
    public function __construct(CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
        $this->lowestTierPrice = null;
    }

    /**
     * Get raw value of "as low as" as a minimal among tier prices{@inheritdoc}
     *
     * @param SaleableInterface $saleableItem
     * @return float|null
     */
    public function getValue(SaleableInterface $saleableItem)
    {
        $this->lowestTierPrice = null;
        /** @var TierPrice $price */
        $tierPrice = $saleableItem->getPriceInfo()->getPrice(TierPrice::PRICE_CODE);
        $tierPriceList = $tierPrice->getTierPriceList();
        $finalPrice = $saleableItem->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();

        $minPrice = $finalPrice;
        foreach ($tierPriceList as $tierPrice) {
            /** @var AmountInterface $price */
            $price = $tierPrice['price'];
            if ($minPrice > $price->getValue()) {
                $minPrice = $price->getValue();
                $this->lowestTierPrice = $price;
            }
        }

        return $this->lowestTierPrice?->getValue();
    }

    /**
     * Return calculated amount object that keeps "as low as" value{@inheritdoc}
     *
     * @param SaleableInterface $saleableItem
     * @return AmountInterface|null
     */
    public function getAmount(SaleableInterface $saleableItem)
    {
        $value = $this->getValue($saleableItem);
        return $value === null ? null : $this->lowestTierPrice;
    }
}
