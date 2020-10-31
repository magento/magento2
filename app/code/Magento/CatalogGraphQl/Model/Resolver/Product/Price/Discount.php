<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Price;

/**
 * Calculate price discount as value and percent
 */
class Discount
{
    /**
     * @var float
     */
    private $zeroThreshold = 0.0001;

    /**
     * Get formatted discount between two prices
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @return array
     */
    public function getDiscountByDifference(float $regularPrice, float $finalPrice): array
    {
        return [
            'amount_off' => $this->getPriceDifferenceAsValue($regularPrice, $finalPrice),
            'percent_off' => $this->getPriceDifferenceAsPercent($regularPrice, $finalPrice)
        ];
    }

    /**
     * Get formatted discount based on percent off
     *
     * @param float $regularPrice
     * @param float $percentOff
     * @return array
     */
    public function getDiscountByPercent(float $regularPrice, float $percentOff): array
    {
        return [
            'amount_off' => $this->getPercentDiscountAsValue($regularPrice, $percentOff),
            'percent_off' => $percentOff
        ];
    }

    /**
     * Get value difference between two prices
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @return float
     */
    private function getPriceDifferenceAsValue(float $regularPrice, float $finalPrice): float
    {
        $difference = $regularPrice - $finalPrice;
        if ($difference <= $this->zeroThreshold) {
            return 0;
        }
        return round($difference, 2);
    }

    /**
     * Get percent difference between two prices
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @return float
     */
    private function getPriceDifferenceAsPercent(float $regularPrice, float $finalPrice): float
    {
        $difference = $this->getPriceDifferenceAsValue($regularPrice, $finalPrice);

        if ($difference <= $this->zeroThreshold || $regularPrice <= $this->zeroThreshold) {
            return 0;
        }

        return round(($difference / $regularPrice) * 100, 2);
    }

    /**
     * Get amount difference that percentOff represents
     *
     * @param float $regularPrice
     * @param float $percentOff
     * @return float
     */
    private function getPercentDiscountAsValue(float $regularPrice, float $percentOff): float
    {
        $percentDecimal = $percentOff / 100;
        $valueDiscount = $regularPrice * $percentDecimal;

        return round($valueDiscount, 2);
    }
}
