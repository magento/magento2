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
 * As Low As shows minimal value of Tier Prices
 * @since 2.2.0
 */
class MinimalTierPriceCalculator implements MinimalPriceCalculatorInterface
{
    /**
     * @var CalculatorInterface
     * @since 2.2.0
     */
    private $calculator;

    /**
     * @param CalculatorInterface $calculator
     * @since 2.2.0
     */
    public function __construct(CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Get raw value of "as low as" as a minimal among tier prices
     * {@inheritdoc}
     * @since 2.2.0
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
     * Return calculated amount object that keeps "as low as" value
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getAmount(SaleableInterface $saleableItem)
    {
        $value = $this->getValue($saleableItem);

        return $value === null
            ? null
            : $this->calculator->getAmount($value, $saleableItem);
    }
}
