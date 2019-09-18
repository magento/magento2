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
     * Get formatted discount between two prices
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @return array
     */
    public function getPriceDiscount(float $regularPrice, float $finalPrice)
    {
        return [
            'amount_off' => $this->getPriceDifferenceAsValue($regularPrice, $finalPrice),
            'percent_off' => $this->getPriceDifferenceAsPercent($regularPrice, $finalPrice)
        ];
    }

    /**
     * Get value difference between two prices
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @return float
     */
    private function getPriceDifferenceAsValue(float $regularPrice, float $finalPrice)
    {
        return round($regularPrice - $finalPrice, 2);
    }

    /**
     * Get percent difference between two prices
     *
     * @param float $regularPrice
     * @param float $finalPrice
     * @return float|int
     */
    private function getPriceDifferenceAsPercent(float $regularPrice, float $finalPrice)
    {
        $difference = $this->getPriceDifferenceAsValue($regularPrice, $finalPrice);

        if ($difference === 0 || $regularPrice === 0) {
            return 0;
        }

        return round(($difference / $regularPrice) * 100, 2);
    }
}
