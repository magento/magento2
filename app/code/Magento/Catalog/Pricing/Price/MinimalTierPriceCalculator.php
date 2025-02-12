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
     * @param CalculatorInterface $calculator
     */
    public function __construct(CalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Get raw value of "as low as" as a minimal among tier prices{@inheritdoc}
     *
     * @param SaleableInterface $saleableItem
     * @return float|null
     */
    public function getValue(SaleableInterface $saleableItem)
    {
        return $this->getAmount($saleableItem)?->getValue();
    }

    /**
     * Return calculated amount object that keeps "as low as" value{@inheritdoc}
     *
     * @param SaleableInterface $saleableItem
     * @return AmountInterface|null
     */
    public function getAmount(SaleableInterface $saleableItem)
    {
        $minPrice = null;
        /** @var TierPrice $price */
        $tierPrice = $saleableItem->getPriceInfo()->getPrice(TierPrice::PRICE_CODE);
        $tierPriceList = $tierPrice->getTierPriceList();

        if (count($tierPriceList)) {
            usort($tierPriceList, fn ($tier1, $tier2) => $tier1['price']->getValue() <=> $tier2['price']->getValue());
            $minPrice = array_shift($tierPriceList)['price'];
        }

        return $minPrice;
    }
}
