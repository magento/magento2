<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Pricing\Price;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * As Low As shows minimal value of Tier Prices
 */
class MinimalTierPriceCalculator extends \Magento\Catalog\Pricing\Price\MinimalTierPriceCalculator
{
    /**
     * Return calculated amount object that keeps "as low as" value
     * {@inheritdoc}
     */
    public function getAmount(SaleableInterface $saleableItem)
    {
        $value = $this->getValue($saleableItem);

        return $value === null
            ? null
            : $this->calculator->getAmount($value, $saleableItem, ['weee', 'weee_tax', 'tax']);
    }
}
